<?php

namespace App\Livewire\Farmacia;

use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProveedoresIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    // Control de modal de creación / edición
    public bool $modalOpen = false;

    public ?int $proveedorId = null;

    public string $razon_social = '';

    public string $nit_ruc = '';

    public string $contacto_nombre = '';

    public string $telefono = '';

    public string $celular = '';

    public string $direccion = '';

    public string $correo = '';

    // Modal de Lotes / Compras Suministradas
    public bool $modalLotesOpen = false;

    public ?int $proveedorDetalleId = null;

    protected function rules(): array
    {
        return [
            'razon_social' => 'required|string|min:3|max:191|unique:proveedores,razon_social,' . $this->proveedorId . ',id,deleted_at,NULL',
            'nit_ruc' => 'nullable|string|max:50',
            'contacto_nombre' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:30',
            'celular' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:150',
        ];
    }

    protected $messages = [
        'razon_social.required' => 'La razón social o nombre de la droguería/proveedor es obligatorio.',
        'razon_social.min' => 'La razón social debe tener al menos 3 caracteres.',
        'razon_social.unique' => 'Ya existe un proveedor registrado con esta razón social.',
        'correo.email' => 'El correo electrónico ingresado no tiene un formato válido.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function abrirModal(?int $id = null): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.catalogos.gestionar')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar proveedores farmacéuticos.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->proveedorId = $id;

        if ($id) {
            $proveedor = Proveedor::findOrFail($id);
            $this->razon_social = $proveedor->razon_social;
            $this->nit_ruc = $proveedor->nit_ruc ?? '';
            $this->contacto_nombre = $proveedor->contacto_nombre ?? '';
            $this->telefono = $proveedor->telefono ?? '';
            $this->celular = $proveedor->celular ?? '';
            $this->direccion = $proveedor->direccion ?? '';
            $this->correo = $proveedor->correo ?? '';
        } else {
            $this->razon_social = '';
            $this->nit_ruc = '';
            $this->contacto_nombre = '';
            $this->telefono = '';
            $this->celular = '';
            $this->direccion = '';
            $this->correo = '';
        }

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->proveedorId = null;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.catalogos.gestionar')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar proveedores farmacéuticos.',
            ]);

            return;
        }

        $this->validate();

        $data = [
            'razon_social' => trim($this->razon_social),
            'nit_ruc' => ! empty($this->nit_ruc) ? trim($this->nit_ruc) : null,
            'contacto_nombre' => ! empty($this->contacto_nombre) ? trim($this->contacto_nombre) : null,
            'telefono' => ! empty($this->telefono) ? trim($this->telefono) : null,
            'celular' => ! empty($this->celular) ? trim($this->celular) : null,
            'direccion' => ! empty($this->direccion) ? trim($this->direccion) : null,
            'correo' => ! empty($this->correo) ? trim($this->correo) : null,
        ];

        if ($this->proveedorId) {
            $proveedor = Proveedor::findOrFail($this->proveedorId);
            $proveedor->update($data);

            $mensaje = 'Datos del proveedor / distribuidora actualizados correctamente.';
        } else {
            Proveedor::create($data);

            $mensaje = 'Nuevo proveedor / distribuidora registrado con éxito.';
        }

        $this->cerrarModal();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Operación Exitosa!',
            'text' => $mensaje,
            'toast' => true,
        ]);
    }

    public function verLotes(int $id): void
    {
        $this->proveedorDetalleId = $id;
        $this->modalLotesOpen = true;
    }

    public function cerrarModalLotes(): void
    {
        $this->modalLotesOpen = false;
        $this->proveedorDetalleId = null;
    }

    public function eliminar(int $id): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.catalogos.gestionar')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar proveedores farmacéuticos.',
            ]);

            return;
        }

        $proveedor = Proveedor::withCount('lotes')->findOrFail($id);

        if ($proveedor->lotes_count > 0) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "El proveedor registra {$proveedor->lotes_count} lote(s) o compra(s) en el inventario. Por integridad y trazabilidad del Kardex no puede ser eliminado.",
                'toast' => false,
            ]);

            return;
        }

        $proveedor->delete();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => 'Proveedor Eliminado',
            'text' => 'El proveedor ha sido eliminado del catálogo.',
            'toast' => true,
        ]);
    }

    public function render(): View
    {
        $proveedores = Proveedor::withCount('lotes')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('razon_social', 'like', "%{$this->search}%")
                        ->orWhere('nit_ruc', 'like', "%{$this->search}%")
                        ->orWhere('contacto_nombre', 'like', "%{$this->search}%")
                        ->orWhere('celular', 'like', "%{$this->search}%")
                        ->orWhere('correo', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('razon_social', 'asc')
            ->paginate($this->perPage);

        $proveedorDetalle = null;
        if ($this->proveedorDetalleId) {
            $proveedorDetalle = Proveedor::with(['lotes.producto', 'lotes.sucursal'])->find($this->proveedorDetalleId);
        }

        $totalProveedores = Proveedor::count();
        $totalLotesAbastecidos = Proveedor::withCount('lotes')->get()->sum('lotes_count');

        return view('livewire.farmacia.proveedores-index', [
            'proveedores' => $proveedores,
            'proveedorDetalle' => $proveedorDetalle,
            'totalProveedores' => $totalProveedores,
            'totalLotesAbastecidos' => $totalLotesAbastecidos,
        ]);
    }
}
