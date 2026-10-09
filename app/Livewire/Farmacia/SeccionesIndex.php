<?php

namespace App\Livewire\Farmacia;

use App\Models\Seccion;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SeccionesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursal = null;

    // Modal Crear / Editar
    public bool $modalOpen = false;

    public ?int $seccionId = null;

    public ?int $sucursal_id = null;

    public string $nombre = '';

    public string $descripcion = '';

    public bool $es_almacen_principal = false;

    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'nombre' => 'required|string|min:2|max:100',
            'descripcion' => 'nullable|string|max:255',
            'es_almacen_principal' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'sucursal_id.required' => 'La sucursal es obligatoria.',
        'nombre.required' => 'El nombre del área o sección es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
    ];

    public function mount(): void
    {
        $this->sucursal_id = Auth::user()->sucursal_id ?? Sucursal::first()?->id;
        $this->filtroSucursal = $this->sucursal_id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    public function abrirModal(?int $id = null): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.secciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar secciones y áreas hospitalarias.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->seccionId = $id;

        if ($id) {
            $seccion = Seccion::findOrFail($id);
            $this->sucursal_id = $seccion->sucursal_id;
            $this->nombre = $seccion->nombre;
            $this->descripcion = $seccion->descripcion ?? '';
            $this->es_almacen_principal = (bool) $seccion->es_almacen_principal;
            $this->activo = (bool) $seccion->activo;
        } else {
            $this->sucursal_id = $this->filtroSucursal ?? Auth::user()->sucursal_id ?? Sucursal::first()?->id;
            $this->nombre = '';
            $this->descripcion = '';
            $this->es_almacen_principal = false;
            $this->activo = true;
        }

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
        $this->reset(['seccionId', 'nombre', 'descripcion', 'es_almacen_principal', 'activo']);
    }

    public function guardar(): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.secciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar secciones y áreas hospitalarias.',
            ]);

            return;
        }

        $this->validate();

        // Si se marca como almacén principal, desmarcar otros en la misma sucursal
        if ($this->es_almacen_principal) {
            Seccion::where('sucursal_id', $this->sucursal_id)
                ->when($this->seccionId, fn($q) => $q->where('id', '!=', $this->seccionId))
                ->update(['es_almacen_principal' => false]);
        }

        if ($this->seccionId) {
            $seccion = Seccion::findOrFail($this->seccionId);
            $seccion->update([
                'sucursal_id' => $this->sucursal_id,
                'nombre' => trim($this->nombre),
                'descripcion' => trim($this->descripcion) ?: null,
                'es_almacen_principal' => $this->es_almacen_principal,
                'activo' => $this->activo,
            ]);

            $mensaje = 'Sección o área médica actualizada con éxito.';
        } else {
            Seccion::create([
                'sucursal_id' => $this->sucursal_id,
                'nombre' => trim($this->nombre),
                'descripcion' => trim($this->descripcion) ?: null,
                'es_almacen_principal' => $this->es_almacen_principal,
                'activo' => $this->activo,
            ]);

            $mensaje = 'Nueva sección o área hospitalaria creada exitosamente.';
        }

        $this->cerrarModal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Operación Exitosa!',
            'text' => $mensaje,
        ]);
    }

    public function toggleActivo(int $id): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.secciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar secciones y áreas hospitalarias.',
            ]);

            return;
        }

        $seccion = Seccion::findOrFail($id);

        if ($seccion->es_almacen_principal && $seccion->activo) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no permitida',
                'text' => 'No puede desactivar la sección designada como Almacén Principal de la sucursal.',
            ]);

            return;
        }

        $seccion->activo = ! $seccion->activo;
        $seccion->save();

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado Actualizado',
            'text' => "El área {$seccion->nombre} ahora se encuentra " . ($seccion->activo ? 'Activa' : 'Inactiva') . '.',
        ]);
    }

    public function eliminar(int $id): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.secciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar secciones y áreas hospitalarias.',
            ]);

            return;
        }

        $seccion = Seccion::with('loteSecciones')->findOrFail($id);

        if ($seccion->es_almacen_principal) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => 'El Almacén Principal de la sucursal no puede ser eliminado.',
            ]);

            return;
        }

        $stockTotal = (int) $seccion->loteSecciones->where('cantidad_actual', '>', 0)->sum('cantidad_actual');
        if ($stockTotal > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Área con Existencias Activas',
                'text' => "Esta sección cuenta con {$stockTotal} unidad(es) de medicamentos o insumos. Debe transferir el stock a otra área antes de archivarla.",
            ]);

            return;
        }

        $seccion->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Área Eliminada',
            'text' => 'La sección hospitalaria fue archivada correctamente.',
        ]);
    }

    public function render(): View
    {
        $query = Seccion::query()
            ->with(['sucursal'])
            ->withSum('loteSecciones as stock_total', 'cantidad_actual');

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if (! empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term);
            });
        }

        $secciones = $query->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        $sucursales = Sucursal::where('estado', true)->orderBy('nombre')->get();

        // Métricas de secciones para la sucursal activa
        $seccionesSucursal = Seccion::where('sucursal_id', $this->filtroSucursal ?? Auth::user()->sucursal_id)->get();
        $totalSecciones = $seccionesSucursal->count();
        $seccionesActivas = $seccionesSucursal->where('activo', true)->count();
        $almacenPrincipal = $seccionesSucursal->firstWhere('es_almacen_principal', true)?->nombre ?? 'No asignado';

        return view('livewire.farmacia.secciones-index', [
            'secciones' => $secciones,
            'sucursales' => $sucursales,
            'totalSecciones' => $totalSecciones,
            'seccionesActivas' => $seccionesActivas,
            'almacenPrincipal' => $almacenPrincipal,
        ]);
    }
}
