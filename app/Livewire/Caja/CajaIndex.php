<?php

namespace App\Livewire\Caja;

use App\Models\Proforma;
use App\Models\ProformaPago;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CajaIndex extends Component
{
    use WithPagination;

    public string $activeTab = 'pendientes'; // pendientes, pagadas, arqueo

    public string $search = '';

    public int $perPage = 10;

    public ?int $sucursalId = null;

    // Control del Modal de Cobro
    public bool $modalCobroOpen = false;

    public ?int $proformaCobroId = null;

    public ?Proforma $proformaCobro = null;

    /**
     * @var array<int, array{tipo_pago: string, monto: string|float, numero_referencia: string}>
     */
    public array $lineasPago = [];

    protected $queryString = [
        'activeTab' => ['except' => 'pendientes'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->sucursalId = Auth::user()->sucursal_id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function cambiarTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Abre el modal de liquidación y cobro para una proforma.
     */
    public function abrirModalCobro(int $proformaId): void
    {
        $proforma = Proforma::with(['paciente', 'servicios', 'movimientosInventario', 'consumosExtras', 'pagos'])->find($proformaId);

        if (! $proforma) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No encontrada',
                'text' => 'La proforma no existe.',
                'toast' => true,
            ]);

            return;
        }

        $proforma->recalcularTotal();
        $proforma->refresh();

        if ($proforma->saldoPendiente() <= 0) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Cuenta sin saldo pendiente',
                'text' => 'Esta proforma ya fue saldada en su totalidad.',
                'toast' => true,
            ]);

            return;
        }

        $this->proformaCobroId = $proforma->id;
        $this->proformaCobro = $proforma;

        // Inicializar con una línea de pago sugerida con el saldo pendiente
        $this->lineasPago = [
            [
                'tipo_pago' => 'Efectivo',
                'monto' => number_format($proforma->saldoPendiente(), 2, '.', ''),
                'numero_referencia' => '',
            ],
        ];

        $this->modalCobroOpen = true;
    }

    public function cerrarModalCobro(): void
    {
        $this->modalCobroOpen = false;
        $this->proformaCobroId = null;
        $this->proformaCobro = null;
        $this->lineasPago = [];
        $this->resetValidation();
    }

    /**
     * Agrega una nueva línea de método de pago.
     */
    public function agregarLineaPago(): void
    {
        $totalYaAsignado = 0.00;
        foreach ($this->lineasPago as $linea) {
            $totalYaAsignado += (float) ($linea['monto'] ?? 0);
        }

        $saldoRestante = max(0.00, ($this->proformaCobro?->saldoPendiente() ?? 0) - $totalYaAsignado);

        $this->lineasPago[] = [
            'tipo_pago' => 'QR',
            'monto' => $saldoRestante > 0 ? number_format($saldoRestante, 2, '.', '') : '',
            'numero_referencia' => '',
        ];
    }

    /**
     * Elimina una línea de pago.
     */
    public function eliminarLineaPago(int $index): void
    {
        if (count($this->lineasPago) > 1) {
            unset($this->lineasPago[$index]);
            $this->lineasPago = array_values($this->lineasPago);
        }
    }

    /**
     * Autocompleta el monto de la línea con el saldo restante.
     */
    public function llenarSaldoRestante(int $index): void
    {
        if (! $this->proformaCobro) {
            return;
        }

        $otrosMontos = 0.00;
        foreach ($this->lineasPago as $i => $linea) {
            if ($i !== $index) {
                $otrosMontos += (float) ($linea['monto'] ?? 0);
            }
        }

        $restante = max(0.00, $this->proformaCobro->saldoPendiente() - $otrosMontos);
        $this->lineasPago[$index]['monto'] = number_format($restante, 2, '.', '');
    }

    /**
     * Procesa y registra las transacciones de pago dentro de una transacción DB.
     */
    public function procesarCobro(): void
    {
        if (! $this->proformaCobro) {
            return;
        }

        $this->proformaCobro->recalcularTotal();
        $this->proformaCobro->refresh();

        $saldoPendiente = $this->proformaCobro->saldoPendiente();

        if ($saldoPendiente <= 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cuenta saldada',
                'text' => 'La proforma no registra saldo pendiente.',
                'toast' => true,
            ]);
            $this->cerrarModalCobro();

            return;
        }

        // Validación de líneas
        $totalCobro = 0.00;
        foreach ($this->lineasPago as $index => $linea) {
            $monto = (float) ($linea['monto'] ?? 0);
            $tipo = $linea['tipo_pago'] ?? '';
            $ref = trim($linea['numero_referencia'] ?? '');

            if ($monto <= 0) {
                $this->addError("lineasPago.{$index}.monto", 'El monto debe ser mayor a 0.');

                return;
            }

            if (! in_array($tipo, ['Efectivo', 'QR', 'Transferencia'], true)) {
                $this->addError("lineasPago.{$index}.tipo_pago", 'Seleccione un método válido.');

                return;
            }

            if (in_array($tipo, ['Transferencia'], true) && empty($ref)) {
                $this->addError("lineasPago.{$index}.numero_referencia", 'N° de comprobante/referencia es obligatorio para pagos electrónicos.');

                return;
            }

            $totalCobro += $monto;
        }

        if (round($totalCobro, 2) > round($saldoPendiente, 2)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Monto Excedido',
                'text' => 'La suma de pagos (Bs. '.number_format($totalCobro, 2).') no puede superar el saldo pendiente (Bs. '.number_format($saldoPendiente, 2).').',
                'toast' => false,
            ]);

            return;
        }

        DB::beginTransaction();
        try {
            foreach ($this->lineasPago as $linea) {
                ProformaPago::create([
                    'proforma_id' => $this->proformaCobro->id,
                    'tipo_pago' => $linea['tipo_pago'],
                    'monto' => (float) $linea['monto'],
                    'numero_referencia' => ! empty($linea['numero_referencia']) ? trim($linea['numero_referencia']) : null,
                    'user_id' => Auth::id(),
                ]);
            }

            $this->proformaCobro->refresh();

            // Si el saldo pendiente quedó saldado (o <= 0)
            if ($this->proformaCobro->saldoPendiente() <= 0.01) {
                $this->proformaCobro->estado = 'Pagada';
                if (! $this->proformaCobro->fecha_salida) {
                    $this->proformaCobro->fecha_salida = now();
                }
                $this->proformaCobro->save();
            }

            DB::commit();

            $proformaId = $this->proformaCobro->id;
            $this->cerrarModalCobro();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Cobro registrado con éxito!',
                'text' => 'Se registraron los pagos por un total de Bs. '.number_format($totalCobro, 2).'. Puede imprimir el recibo de caja y el detalle de cuenta.',
                'toast' => false,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al procesar pago',
                'text' => $e->getMessage(),
                'toast' => false,
            ]);
        }
    }

    /**
     * Estadísticas del arqueo diario en la sucursal del cajero.
     */
    public function getArqueoHoyProperty(): array
    {
        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        $pagosHoyQuery = ProformaPago::whereDate('proforma_pagos.created_at', today())
            ->whereHas('proforma', function ($q) use ($sucursalId) {
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            });

        $totalGeneral = (float) (clone $pagosHoyQuery)->sum('monto');
        $totalEfectivo = (float) (clone $pagosHoyQuery)->where('tipo_pago', 'Efectivo')->sum('monto');
        $totalQr = (float) (clone $pagosHoyQuery)->where('tipo_pago', 'QR')->sum('monto');
        $totalTransferencia = (float) (clone $pagosHoyQuery)->where('tipo_pago', 'Transferencia')->sum('monto');
        $cantidadTransacciones = (clone $pagosHoyQuery)->count();

        return [
            'total_general' => $totalGeneral,
            'total_efectivo' => $totalEfectivo,
            'total_qr' => $totalQr,
            'total_transferencia' => $totalTransferencia,
            'cantidad_transacciones' => $cantidadTransacciones,
        ];
    }

    public function render(): View
    {
        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        $proformasQuery = Proforma::with(['paciente', 'sucursal', 'pagos', 'servicios', 'movimientosInventario', 'consumosExtras'])
            ->where('estado', '!=', 'Anulada')
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                        ->orWhere('diagnostico', 'like', "%{$this->search}%")
                        ->orWhereHas('paciente', function ($qp) {
                            $qp->where('nombres', 'like', "%{$this->search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                                ->orWhere('apellido_materno', 'like', "%{$this->search}%")
                                ->orWhere('cedula', 'like', "%{$this->search}%");
                        });
                });
            });

        if ($this->activeTab === 'pendientes') {
            // Proformas con costo_total > 0 que no están totalmente pagadas
            $proformasQuery->where('estado', '!=', 'Pagada')
                ->orderBy('created_at', 'desc');
        } elseif ($this->activeTab === 'pagadas') {
            // Proformas con estado Pagada o con pagos registrados
            $proformasQuery->where('estado', 'Pagada')
                ->orderBy('updated_at', 'desc');
        } else {
            // Tab arqueo o default
            $proformasQuery->orderBy('created_at', 'desc');
        }

        $proformas = $proformasQuery->paginate($this->perPage);

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('livewire.caja.caja-index', [
            'proformas' => $proformas,
            'sucursales' => $sucursales,
            'arqueoHoy' => $this->arqueoHoy,
        ]);
    }
}
