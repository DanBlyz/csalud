<?php

namespace App\Livewire\Proformas;

use App\Models\Proforma;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProformasIndex extends Component
{
    use WithPagination;

    // Filtros de tabla
    public int $perPage = 10;

    public string $search = '';

    public string $filtroEstado = '';

    public string $filtroTipo = '';

    public ?int $filtroSucursal = null;

    public function mount(): void
    {
        // Por defecto, sucursal del usuario
        $user = Auth::user();
        if ($user && $user->sucursal_id) {
            $this->filtroSucursal = $user->sucursal_id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    #[On('anularProforma')]
    public function anularProforma(int $id): void
    {
        $proforma = Proforma::findOrFail($id);

        if ($proforma->estado === 'Pagada') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Operación Inválida',
                'text' => 'No se puede anular una proforma que ya ha sido pagada y liquidada.',
            ]);

            return;
        }

        $proforma->update(['estado' => 'Anulada']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Proforma Anulada',
            'text' => "La proforma #{$proforma->id} ha sido marcada como Anulada.",
        ]);
    }

    #[On('eliminarProforma')]
    public function eliminarProforma(int $id): void
    {
        $proforma = Proforma::findOrFail($id);

        if ($proforma->estado === 'Pagada') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción Denegada',
                'text' => 'Una proforma pagada no puede ser eliminada por motivos contables.',
            ]);

            return;
        }

        $proforma->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Proforma Eliminada',
            'text' => "La proforma #{$id} ha sido eliminada.",
        ]);
    }

    public function render(): View
    {
        $query = Proforma::query()
            ->with(['paciente', 'medicos.especialidad', 'sucursal'])
            ->withCount(['servicios', 'solicitudes', 'recetas', 'consumosExtras'])
            ->when($this->filtroSucursal, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursal);
            })
            ->when($this->filtroEstado !== '', function ($q) {
                $q->where('estado', $this->filtroEstado);
            })
            ->when($this->filtroTipo !== '', function ($q) {
                $q->where('tipo_atencion', $this->filtroTipo);
            })
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('id', 'like', $term)
                        ->orWhere('pieza', 'like', $term)
                        ->orWhere('motivo_consulta', 'like', $term)
                        ->orWhereHas('paciente', function ($p) use ($term) {
                            $p->where('nombres', 'like', $term)
                                ->orWhere('apellido_paterno', 'like', $term)
                                ->orWhere('apellido_materno', 'like', $term)
                                ->orWhere('cedula', 'like', $term);
                        })
                        ->orWhereHas('medicos', function ($m) use ($term) {
                            $m->where('nombres', 'like', $term)
                                ->orWhere('apellido_paterno', 'like', $term);
                        });
                });
            })
            ->latest('id');

        $proformas = $query->paginate($this->perPage);

        // Métricas de estado
        $sucursalId = $this->filtroSucursal;
        $totalEnCurso = Proforma::when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))->where('estado', 'En Curso')->count();
        $totalInternados = Proforma::when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))->where('estado', 'En Curso')->where('tipo_atencion', 'Internacion')->count();
        $totalAmbulatoriasHoy = Proforma::when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))->where('tipo_atencion', 'Ambulatoria')->whereDate('fecha_ingreso', today())->count();
        $totalPagadas = Proforma::when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))->where('estado', 'Pagada')->count();

        $sucursales = Sucursal::where('estado', true)->get();

        return view('livewire.proformas.proformas-index', [
            'proformas' => $proformas,
            'totalEnCurso' => $totalEnCurso,
            'totalInternados' => $totalInternados,
            'totalAmbulatoriasHoy' => $totalAmbulatoriasHoy,
            'totalPagadas' => $totalPagadas,
            'sucursales' => $sucursales,
        ]);
    }
}
