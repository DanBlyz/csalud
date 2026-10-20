<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\Seccion;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DashboardIndex extends Component
{
    use WithPagination;

    // Filtros de fecha y sucursal
    public string $fecha = '';

    public ?int $filtroSucursal = null;

    // Filtros de inventario
    public ?int $filtroSeccion = null;

    public string $buscarMedicamento = '';

    public int $umbralStock = 10;

    public int $perPage = 10;

    public function mount(): void
    {
        $this->fecha = today()->format('Y-m-d');

        $user = Auth::user();
        if ($user && $user->sucursal_id) {
            $this->filtroSucursal = $user->sucursal_id;
        } elseif ($user && ! $user->esAdmin()) {
            $this->filtroSucursal = Sucursal::value('id');
        }
    }

    public function updatingBuscarMedicamento(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSeccion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->filtroSeccion = null;
        $this->resetPage();
    }

    public function updatingUmbralStock(): void
    {
        $this->resetPage();
    }

    public function updatingFecha(): void
    {
        $this->resetPage();
    }

    public function setHoy(): void
    {
        $this->fecha = today()->format('Y-m-d');
        $this->resetPage();
    }

    public function seleccionarSeccion(?int $seccionId): void
    {
        $this->filtroSeccion = $seccionId;
        $this->resetPage();
    }

    public function render(): View
    {
        $user = Auth::user();

        // 1. Catálogos para filtros
        $sucursales = Sucursal::orderBy('nombre')->get();

        $seccionesDisponibles = Seccion::query()
            ->where('activo', true)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre')
            ->get();

        // 2. Tarjetas de métricas
        // Total histórico de pacientes registrados en el sistema
        $totalPacientes = Paciente::count();

        // Fecha de consulta seleccionada (o hoy por defecto)
        $fechaConsulta = ! empty($this->fecha) ? $this->fecha : today()->format('Y-m-d');

        // Pacientes que se internaron ese día
        $pacientesInternadosHoy = Proforma::query()
            ->where('estado', '!=', 'Anulada')
            ->where('tipo_atencion', 'Internacion')
            ->whereDate('fecha_ingreso', $fechaConsulta)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();

        // Pacientes que se atendieron pero NO se internaron ese día (Atención Ambulatoria)
        $pacientesAmbulatoriosHoy = Proforma::query()
            ->where('estado', '!=', 'Anulada')
            ->where('tipo_atencion', 'Ambulatoria')
            ->whereDate('fecha_ingreso', $fechaConsulta)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();

        // Pacientes dados de alta médica ese día
        $pacientesAltasHoy = Proforma::query()
            ->where('estado', '!=', 'Anulada')
            ->whereNotNull('fecha_salida')
            ->whereDate('fecha_salida', $fechaConsulta)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();

        // 3. Sincronización proactiva de lotes sin lote_secciones (integridad con fábricas o lotes heredados)
        $this->asegurarLotesConSeccion();

        // 4. Medicamentos por agotarse según sección
        $queryMedicamentos = LoteSeccion::query()
            ->with(['lote.producto.marca', 'lote.sucursal', 'seccion'])
            ->whereHas('lote', function ($q) {
                $q->whereNull('deleted_at');
                if ($this->filtroSucursal) {
                    $q->where('sucursal_id', $this->filtroSucursal);
                }
            })
            ->when($this->filtroSeccion, fn ($q) => $q->where('seccion_id', $this->filtroSeccion))
            ->when($this->buscarMedicamento, function ($q) {
                $term = '%'.trim($this->buscarMedicamento).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('lote.producto', fn ($pq) => $pq->where('nombre', 'like', $term))
                        ->orWhereHas('lote', fn ($lq) => $lq->where('codigo_lote', 'like', $term));
                });
            })
            ->where(function ($q) {
                $q->where('cantidad_actual', '<=', $this->umbralStock)
                    ->orWhereHas('lote.producto', function ($pq) {
                        $pq->whereColumn('lote_secciones.cantidad_actual', '<=', 'productos.stock_minimo');
                    });
            })
            ->orderBy('cantidad_actual', 'asc')
            ->orderBy('id', 'asc');

        // Conteo rápido de alertas
        $totalAgotados = (clone $queryMedicamentos)->where('cantidad_actual', '<=', 0)->count();
        $totalCriticos = (clone $queryMedicamentos)->where('cantidad_actual', '>', 0)->where('cantidad_actual', '<=', 5)->count();

        $medicamentosPorAgotarse = $queryMedicamentos->paginate($this->perPage);

        // Formato legible de la fecha activa
        $fechaCarbon = Carbon::parse($fechaConsulta)->locale('es');
        $fechaTexto = $fechaCarbon->translatedFormat('l, d \d\e F \d\e Y');

        return view('livewire.dashboard-index', [
            'totalPacientes' => $totalPacientes,
            'pacientesInternadosHoy' => $pacientesInternadosHoy,
            'pacientesAmbulatoriosHoy' => $pacientesAmbulatoriosHoy,
            'pacientesAltasHoy' => $pacientesAltasHoy,
            'medicamentosPorAgotarse' => $medicamentosPorAgotarse,
            'totalAgotados' => $totalAgotados,
            'totalCriticos' => $totalCriticos,
            'sucursales' => $sucursales,
            'seccionesDisponibles' => $seccionesDisponibles,
            'fechaTexto' => ucfirst($fechaTexto),
        ]);
    }

    /**
     * Garantiza que cualquier lote existente en el sistema cuente con al menos un asiento en lote_secciones.
     */
    protected function asegurarLotesConSeccion(): void
    {
        $lotesSinSeccion = Lote::whereDoesntHave('loteSecciones')->get();

        foreach ($lotesSinSeccion as $lote) {
            $seccion = Seccion::where('sucursal_id', $lote->sucursal_id)
                ->where('es_almacen_principal', true)
                ->first() ?? Seccion::where('sucursal_id', $lote->sucursal_id)->first();

            if (! $seccion) {
                $seccion = Seccion::create([
                    'sucursal_id' => $lote->sucursal_id,
                    'nombre' => 'Farmacia Central',
                    'es_almacen_principal' => true,
                    'activo' => true,
                ]);
            }

            LoteSeccion::firstOrCreate(
                ['lote_id' => $lote->id, 'seccion_id' => $seccion->id],
                ['cantidad_actual' => $lote->cantidad_actual]
            );
        }
    }
}
