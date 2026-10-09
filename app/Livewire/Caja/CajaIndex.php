<?php

namespace App\Livewire\Caja;

use App\Models\Caja;
use App\Models\Pago;
use App\Models\Proforma;
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

    public string $activeTab = 'pendientes'; // pendientes, pagadas, movimientos, arqueo

    public string $search = '';

    public int $perPage = 10;

    public ?int $sucursalId = null;

    // Control del Modal de Cobro de Proforma
    public bool $modalCobroOpen = false;

    public ?int $proformaCobroId = null;

    public ?Proforma $proformaCobro = null;

    /**
     * @var array<int, array{tipo_pago: string, monto: string|float, numero_referencia: string}>
     */
    public array $lineasPago = [];

    // Control del Modal de Apertura de Caja
    public bool $modalAperturaOpen = false;

    public string $monto_apertura = '0.00';

    public string $observaciones_apertura = '';

    // Control del Modal de Movimiento Extra (Ingreso Extra / Salida de Caja)
    public bool $modalMovimientoOpen = false;

    public string $mov_tipo_movimiento = 'Ingreso Extra'; // Ingreso Extra, Egreso Caja

    public string $mov_categoria = 'Extra'; // Extra, Gasto Operativo, Servicio Básico, Insumos, Sueldo, Honorario, Otro

    public string $mov_tipo_pago = 'Efectivo'; // Efectivo, QR, Transferencia

    public string $mov_concepto = '';

    public string $mov_monto = '';

    public string $mov_numero_referencia = '';

    // Control del Modal de Cierre de Caja / Arqueo
    public bool $modalCierreOpen = false;

    public string $cierre_efectivo = '';

    public string $cierre_qr = '';

    public string $cierre_transferencia = '';

    public string $cierre_observaciones = '';

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
     * Obtiene la caja actualmente abierta del usuario autenticado en la sucursal seleccionada.
     */
    public function getCajaActivaProperty(): ?Caja
    {
        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        return Caja::where('user_id', Auth::id())
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'Abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    // =========================================================================
    // 1. APERTURA DE CAJA
    // =========================================================================

    public function abrirModalApertura(): void
    {
        if (! Auth::user()?->tienePermiso('caja.aperturar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para aperturar cajas.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->monto_apertura = '0.00';
        $this->observaciones_apertura = '';
        $this->modalAperturaOpen = true;
    }

    public function cerrarModalApertura(): void
    {
        $this->modalAperturaOpen = false;
        $this->resetValidation();
    }

    public function aperturarCaja(): void
    {
        if (! Auth::user()?->tienePermiso('caja.aperturar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para aperturar cajas.',
            ]);

            return;
        }

        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        if (! $sucursalId) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Sucursal No Definida',
                'text' => 'Debe pertenecer a una sucursal para aperturar una caja.',
            ]);

            return;
        }

        if ($this->cajaActiva) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Caja Ya Abierta',
                'text' => "Ya cuenta con una caja abierta activa en esta sucursal (Turno #{$this->cajaActiva->id}).",
            ]);
            $this->cerrarModalApertura();

            return;
        }

        $this->validate([
            'monto_apertura' => ['required', 'numeric', 'min:0'],
            'observaciones_apertura' => ['nullable', 'string', 'max:500'],
        ], [
            'monto_apertura.required' => 'El monto de apertura es requerido (ingrese 0.00 si inicia sin fondo).',
            'monto_apertura.min' => 'El monto inicial no puede ser negativo.',
        ]);

        Caja::create([
            'sucursal_id' => $sucursalId,
            'user_id' => Auth::id(),
            'monto_apertura' => (float) $this->monto_apertura,
            'fecha_apertura' => now(),
            'estado' => 'Abierta',
            'observaciones_apertura' => $this->observaciones_apertura ? trim($this->observaciones_apertura) : null,
        ]);

        $this->cerrarModalApertura();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Caja Aperturada con Éxito!',
            'text' => 'Se ha abierto la caja con un fondo inicial de Bs. ' . number_format((float) $this->monto_apertura, 2) . '. Ya puede registrar cobros y movimientos.',
        ]);
    }

    // =========================================================================
    // 2. MOVIMIENTOS EXTRAS (INGRESOS EXTRAS Y SALIDAS / EGRESOS DE CAJA)
    // =========================================================================

    public function abrirModalMovimiento(string $tipo = 'Ingreso Extra'): void
    {
        if (! Auth::user()?->tienePermiso('caja.movimientos.extra')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para registrar ingresos extraordinarios o egresos de caja.',
            ]);

            return;
        }

        if (! $this->cajaActiva) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Caja Requerida',
                'text' => 'Debe aperturar su caja antes de registrar movimientos extras o egresos.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->mov_tipo_movimiento = in_array($tipo, ['Ingreso Extra', 'Egreso Caja'], true) ? $tipo : 'Ingreso Extra';
        $this->mov_categoria = $this->mov_tipo_movimiento === 'Ingreso Extra' ? 'Extra' : 'Gasto Operativo';
        $this->mov_tipo_pago = 'Efectivo';
        $this->mov_concepto = '';
        $this->mov_monto = '';
        $this->mov_numero_referencia = '';
        $this->modalMovimientoOpen = true;
    }

    public function cerrarModalMovimiento(): void
    {
        $this->modalMovimientoOpen = false;
        $this->resetValidation();
    }

    public function guardarMovimiento(): void
    {
        if (! Auth::user()?->tienePermiso('caja.movimientos.extra')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para registrar ingresos extraordinarios o egresos de caja.',
            ]);

            return;
        }

        $caja = $this->cajaActiva;

        if (! $caja) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Caja No Disponible',
                'text' => 'No tiene una caja abierta activa para asentar este movimiento.',
            ]);
            $this->cerrarModalMovimiento();

            return;
        }

        $this->validate([
            'mov_tipo_movimiento' => ['required', 'in:Ingreso Extra,Egreso Caja'],
            'mov_categoria' => ['required', 'string', 'max:50'],
            'mov_tipo_pago' => ['required', 'in:Efectivo,QR,Transferencia'],
            'mov_concepto' => ['required', 'string', 'min:3', 'max:255'],
            'mov_monto' => ['required', 'numeric', 'min:0.01'],
            'mov_numero_referencia' => ['nullable', 'string', 'max:100'],
        ], [
            'mov_concepto.required' => 'La descripción o concepto del movimiento es obligatorio.',
            'mov_monto.min' => 'El monto debe ser mayor a 0.',
        ]);

        if (in_array($this->mov_tipo_pago, ['Transferencia']) && empty(trim($this->mov_numero_referencia))) {
            $this->addError('mov_numero_referencia', 'El número de comprobante o referencia es obligatorio para transferencias.');

            return;
        }

        $monto = (float) $this->mov_monto;

        // Si es salida en efectivo, validar disponibilidad física en gaveta
        if ($this->mov_tipo_movimiento === 'Egreso Caja' && $this->mov_tipo_pago === 'Efectivo') {
            $saldoEfectivo = $caja->saldoEsperadoEfectivo();
            if ($monto > $saldoEfectivo) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Saldo Insuficiente en Caja',
                    'text' => 'No puede retirar Bs. ' . number_format($monto, 2) . ' porque solo dispone de Bs. ' . number_format($saldoEfectivo, 2) . ' en efectivo en esta caja.',
                ]);

                return;
            }
        }

        Pago::create([
            'caja_id' => $caja->id,
            'proforma_id' => null,
            'tipo_movimiento' => $this->mov_tipo_movimiento,
            'categoria' => trim($this->mov_categoria),
            'tipo_pago' => $this->mov_tipo_pago,
            'concepto' => trim($this->mov_concepto),
            'monto' => $monto,
            'numero_referencia' => ! empty($this->mov_numero_referencia) ? trim($this->mov_numero_referencia) : null,
            'user_id' => Auth::id(),
        ]);

        $this->cerrarModalMovimiento();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->mov_tipo_movimiento === 'Ingreso Extra' ? '¡Ingreso Extra Registrado!' : '¡Salida de Caja Registrada!',
            'text' => "Se asentó {$this->mov_tipo_movimiento} por Bs. " . number_format($monto, 2) . '.',
        ]);
    }

    // =========================================================================
    // 3. CIERRE DE CAJA Y ARQUEO
    // =========================================================================

    public function abrirModalCierre(): void
    {
        if (! Auth::user()?->tienePermiso('caja.cerrar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para realizar cierres o arqueos de caja.',
            ]);

            return;
        }

        $caja = $this->cajaActiva;

        if (! $caja) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Sin Caja Abierta',
                'text' => 'No cuenta con ninguna caja abierta activa para realizar el cierre.',
            ]);

            return;
        }

        $this->resetValidation();
        // Sugerir los saldos calculados automáticamente por el sistema
        $this->cierre_efectivo = number_format($caja->saldoEsperadoEfectivo(), 2, '.', '');
        $this->cierre_qr = number_format($caja->saldoEsperadoQr(), 2, '.', '');
        $this->cierre_transferencia = number_format($caja->saldoEsperadoTransferencia(), 2, '.', '');
        $this->cierre_observaciones = '';
        $this->modalCierreOpen = true;
    }

    public function cerrarModalCierre(): void
    {
        $this->modalCierreOpen = false;
        $this->resetValidation();
    }

    public function ejecutarCierreCaja(): void
    {
        if (! Auth::user()?->tienePermiso('caja.cerrar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para realizar cierres o arqueos de caja.',
            ]);

            return;
        }

        $caja = $this->cajaActiva;

        if (! $caja) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Sesión',
                'text' => 'No se encontró la caja activa a cerrar.',
            ]);
            $this->cerrarModalCierre();

            return;
        }

        $this->validate([
            'cierre_efectivo' => ['required', 'numeric', 'min:0'],
            'cierre_qr' => ['required', 'numeric', 'min:0'],
            'cierre_transferencia' => ['required', 'numeric', 'min:0'],
            'cierre_observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'cierre_efectivo.required' => 'Debe declarar el total contado en efectivo.',
            'cierre_qr.required' => 'Debe declarar el total recaudado por QR.',
            'cierre_transferencia.required' => 'Debe declarar el total recaudado por transferencia.',
        ]);

        $caja->cerrar(
            (float) $this->cierre_efectivo,
            (float) $this->cierre_qr,
            (float) $this->cierre_transferencia,
            $this->cierre_observaciones
        );

        $cajaIdCerrada = $caja->id;

        $formatearDif = function (float $dif, string $nombre): string {
            if (round($dif, 2) == 0) {
                return "{$nombre}: Cuadrado";
            }

            return $dif > 0
                ? "{$nombre}: Sobrante (+Bs. " . number_format($dif, 2) . ')'
                : "{$nombre}: Faltante (-Bs. " . number_format(abs($dif), 2) . ')';
        };

        $resumenDif = implode(' | ', [
            $formatearDif((float) $caja->diferencia_efectivo, 'Efectivo'),
            $formatearDif((float) $caja->diferencia_qr, 'QR'),
            $formatearDif((float) $caja->diferencia_transferencia, 'Transferencia'),
        ]);

        $this->cerrarModalCierre();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Caja Cerrada Exitosamente!',
            'text' => "Se completó el cierre del Turno #{$cajaIdCerrada}. Balance: {$resumenDif}. Puede imprimir el comprobante oficial de arqueo.",
        ]);
    }

    // =========================================================================
    // 4. COBRO DE PROFORMAS
    // =========================================================================

    /**
     * Abre el modal de liquidación y cobro para una proforma.
     */
    public function abrirModalCobro(int $proformaId): void
    {
        if (! Auth::user()?->tienePermiso('caja.cobrar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para realizar cobros en caja.',
            ]);

            return;
        }

        if (! $this->cajaActiva) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Apertura de Caja Requerida',
                'text' => 'Debe aperturar su caja antes de poder realizar cobros de proformas.',
            ]);

            return;
        }

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
        if (! Auth::user()?->tienePermiso('caja.cobrar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para procesar cobros en caja.',
            ]);

            return;
        }

        $caja = $this->cajaActiva;

        if (! $caja) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Caja No Disponible',
                'text' => 'No tiene una caja abierta activa para asentar este cobro.',
            ]);
            $this->cerrarModalCobro();

            return;
        }

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
                $this->addError("lineasPago.{$index}.numero_referencia", 'N° de comprobante/referencia es obligatorio para transferencias.');

                return;
            }

            $totalCobro += $monto;
        }

        if (round($totalCobro, 2) > round($saldoPendiente, 2)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Monto Excedido',
                'text' => 'La suma de pagos (Bs. ' . number_format($totalCobro, 2) . ') no puede superar el saldo pendiente (Bs. ' . number_format($saldoPendiente, 2) . ').',
                'toast' => false,
            ]);

            return;
        }

        DB::beginTransaction();
        try {
            $pacienteNombre = $this->proformaCobro->paciente?->nombre_completo ?? 'Paciente';

            foreach ($this->lineasPago as $linea) {
                Pago::create([
                    'caja_id' => $caja->id,
                    'proforma_id' => $this->proformaCobro->id,
                    'tipo_movimiento' => 'Ingreso Proforma',
                    'categoria' => 'Proforma',
                    'tipo_pago' => $linea['tipo_pago'],
                    'concepto' => "Cobro Proforma #{$this->proformaCobro->id} ({$pacienteNombre})",
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

            $this->cerrarModalCobro();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Cobro registrado con éxito!',
                'text' => 'Se registraron los pagos por un total de Bs. ' . number_format($totalCobro, 2) . '. Puede imprimir el recibo de caja y el detalle de cuenta.',
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
     * Estadísticas del arqueo de hoy / caja activa.
     */
    public function getArqueoHoyProperty(): array
    {
        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        // Si el usuario tiene una caja abierta activa, mostrar los datos de su sesión activa
        if ($this->cajaActiva) {
            $caja = $this->cajaActiva;

            return [
                'caja_activa' => true,
                'caja_id' => $caja->id,
                'monto_apertura' => (float) $caja->monto_apertura,
                'total_general' => $caja->totalIngresos(),
                'total_efectivo' => $caja->totalIngresosEfectivo(),
                'total_qr' => $caja->totalIngresosQr(),
                'total_transferencia' => $caja->totalIngresosTransferencia(),
                'total_egresos' => $caja->totalEgresos(),
                'total_egresos_efectivo' => $caja->totalEgresosEfectivo(),
                'total_egresos_qr' => $caja->totalEgresosQr(),
                'total_egresos_transferencia' => $caja->totalEgresosTransferencia(),
                'saldo_esperado_efectivo' => $caja->saldoEsperadoEfectivo(),
                'saldo_esperado_qr' => $caja->saldoEsperadoQr(),
                'saldo_esperado_transferencia' => $caja->saldoEsperadoTransferencia(),
                'saldo_esperado_total' => $caja->saldoEsperadoTotal(),
                'cantidad_transacciones' => $caja->pagos()->count(),
            ];
        }

        // Estadísticas consolidadas del día en la sucursal
        $pagosHoyQuery = Pago::whereDate('created_at', today())
            ->where(function ($q) use ($sucursalId) {
                if ($sucursalId) {
                    $q->whereHas('caja', fn($c) => $c->where('sucursal_id', $sucursalId))
                        ->orWhereHas('proforma', fn($p) => $p->where('sucursal_id', $sucursalId));
                }
            });

        $totalIngresos = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', '!=', 'Egreso Caja')->sum('monto');
        $totalEfectivo = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', '!=', 'Egreso Caja')->where('tipo_pago', 'Efectivo')->sum('monto');
        $totalQr = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', '!=', 'Egreso Caja')->where('tipo_pago', 'QR')->sum('monto');
        $totalTransferencia = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', '!=', 'Egreso Caja')->where('tipo_pago', 'Transferencia')->sum('monto');
        $totalEgresos = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', 'Egreso Caja')->sum('monto');
        $totalEgresosEf = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', 'Egreso Caja')->where('tipo_pago', 'Efectivo')->sum('monto');
        $totalEgresosQr = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', 'Egreso Caja')->where('tipo_pago', 'QR')->sum('monto');
        $totalEgresosTr = (float) (clone $pagosHoyQuery)->where('tipo_movimiento', 'Egreso Caja')->where('tipo_pago', 'Transferencia')->sum('monto');
        $cantidadTransacciones = (clone $pagosHoyQuery)->count();

        return [
            'caja_activa' => false,
            'caja_id' => null,
            'monto_apertura' => 0.00,
            'total_general' => $totalIngresos,
            'total_efectivo' => $totalEfectivo,
            'total_qr' => $totalQr,
            'total_transferencia' => $totalTransferencia,
            'total_egresos' => $totalEgresos,
            'total_egresos_efectivo' => $totalEgresosEf,
            'total_egresos_qr' => $totalEgresosQr,
            'total_egresos_transferencia' => $totalEgresosTr,
            'saldo_esperado_efectivo' => max(0.00, $totalEfectivo - $totalEgresosEf),
            'saldo_esperado_qr' => max(0.00, $totalQr - $totalEgresosQr),
            'saldo_esperado_transferencia' => max(0.00, $totalTransferencia - $totalEgresosTr),
            'saldo_esperado_total' => max(0.00, ($totalEfectivo - $totalEgresosEf) + ($totalQr - $totalEgresosQr) + ($totalTransferencia - $totalEgresosTr)),
            'cantidad_transacciones' => $cantidadTransacciones,
        ];
    }

    public function render(): View
    {
        $sucursalId = $this->sucursalId ?? Auth::user()->sucursal_id;

        $proformas = null;
        $movimientos = null;
        $cajasHistoricas = null;

        if ($this->activeTab === 'pendientes' || $this->activeTab === 'pagadas') {
            $proformasQuery = Proforma::with(['paciente', 'sucursal', 'pagos', 'servicios', 'movimientosInventario', 'consumosExtras'])
                ->where('estado', '!=', 'Anulada')
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
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
                $proformasQuery->where('estado', '!=', 'Pagada')
                    ->orderBy('created_at', 'desc');
            } else {
                $proformasQuery->where('estado', 'Pagada')
                    ->orderBy('updated_at', 'desc');
            }

            $proformas = $proformasQuery->paginate($this->perPage);
        } elseif ($this->activeTab === 'movimientos') {
            $movimientosQuery = Pago::with(['caja.user', 'proforma.paciente', 'user'])
                ->when($this->cajaActiva, function ($q) {
                    $q->where('caja_id', $this->cajaActiva->id);
                }, function ($q) use ($sucursalId) {
                    if ($sucursalId) {
                        $q->whereHas('caja', fn($c) => $c->where('sucursal_id', $sucursalId));
                    }
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('concepto', 'like', "%{$this->search}%")
                            ->orWhere('categoria', 'like', "%{$this->search}%")
                            ->orWhere('numero_referencia', 'like', "%{$this->search}%")
                            ->orWhere('tipo_pago', 'like', "%{$this->search}%")
                            ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', "%{$this->search}%"));
                    });
                })
                ->orderBy('created_at', 'desc');

            $movimientos = $movimientosQuery->paginate($this->perPage);
        } else {
            // Tab arqueo: Historial de sesiones de caja
            $cajasHistoricasQuery = Caja::with(['user', 'sucursal'])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('id', 'like', "%{$this->search}%")
                            ->orWhere('observaciones_cierre', 'like', "%{$this->search}%")
                            ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', "%{$this->search}%"));
                    });
                })
                ->orderBy('created_at', 'desc');

            $cajasHistoricas = $cajasHistoricasQuery->paginate($this->perPage);
        }

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('livewire.caja.caja-index', [
            'proformas' => $proformas,
            'movimientos' => $movimientos,
            'cajasHistoricas' => $cajasHistoricas,
            'sucursales' => $sucursales,
            'arqueoHoy' => $this->arqueoHoy,
            'cajaActiva' => $this->cajaActiva,
        ]);
    }
}
