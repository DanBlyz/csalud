<?php

namespace App\Livewire\Pacientes;

use App\Models\Paciente;
use App\Models\Proforma;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PacientesIndex extends Component
{
    use WithPagination;

    // Controles de tabla
    public int $perPage = 10;

    public string $search = '';

    public string $filtroGenero = '';

    // Modal Crear/Editar Paciente
    public bool $modalOpen = false;

    public ?int $pacienteId = null;

    public string $nombres = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $cedula = '';

    public ?string $fecha_nacimiento = null;

    public string $genero = 'Masculino';

    public string $direccion = '';

    public string $celular = '';

    public string $contacto_emergencia_nombre = '';

    public string $contacto_emergencia_telefono = '';

    public string $antecedentes_alergias = '';

    // Modal Historial de Proformas
    public bool $modalHistorialOpen = false;

    public ?Paciente $pacienteHistorial = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroGenero(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'min:2', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'min:2', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'cedula' => [
                'required',
                'string',
                'max:30',
                Rule::unique('pacientes', 'cedula')->ignore($this->pacienteId)->whereNull('deleted_at'),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'genero' => ['required', 'in:Masculino,Femenino,Otro'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'celular' => ['nullable', 'string', 'max:20'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_telefono' => ['nullable', 'string', 'max:20'],
            'antecedentes_alergias' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected array $messages = [
        'nombres.required' => 'El nombre del paciente es obligatorio.',
        'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
        'cedula.required' => 'El documento de identidad es obligatorio.',
        'cedula.unique' => 'Ya existe un paciente registrado con este documento de identidad.',
        'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'genero.required' => 'Debe seleccionar un género.',
    ];

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->reset([
            'pacienteId',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'cedula',
            'fecha_nacimiento',
            'genero',
            'direccion',
            'celular',
            'contacto_emergencia_nombre',
            'contacto_emergencia_telefono',
            'antecedentes_alergias',
        ]);
        $this->genero = 'Masculino';
        $this->modalOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetValidation();
        $paciente = Paciente::findOrFail($id);

        $this->pacienteId = $paciente->id;
        $this->nombres = $paciente->nombres;
        $this->apellido_paterno = $paciente->apellido_paterno;
        $this->apellido_materno = $paciente->apellido_materno ?? '';
        $this->cedula = $paciente->cedula ?? '';
        $this->fecha_nacimiento = $paciente->fecha_nacimiento?->format('Y-m-d');
        $this->genero = $paciente->genero ?? 'Masculino';
        $this->direccion = $paciente->direccion ?? '';
        $this->celular = $paciente->celular ?? '';
        $this->contacto_emergencia_nombre = $paciente->contacto_emergencia_nombre ?? '';
        $this->contacto_emergencia_telefono = $paciente->contacto_emergencia_telefono ?? '';
        $this->antecedentes_alergias = $paciente->antecedentes_alergias ?? '';

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->pacienteId) {
            $paciente = Paciente::findOrFail($this->pacienteId);
            $paciente->update($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Paciente Actualizado!',
                'text' => "El paciente {$paciente->nombre_completo} ha sido modificado exitosamente.",
            ]);
        } else {
            $paciente = Paciente::create($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Paciente Registrado!',
                'text' => "El paciente {$paciente->nombre_completo} ha sido registrado en el sistema.",
            ]);
        }

        $this->cerrarModal();
    }

    public function verHistorial(int $id): void
    {
        $this->pacienteHistorial = Paciente::with(['proformas' => function ($q) {
            $q->with(['medicos', 'sucursal'])->latest();
        }])->findOrFail($id);

        $this->modalHistorialOpen = true;
    }

    public function cerrarModalHistorial(): void
    {
        $this->modalHistorialOpen = false;
        $this->pacienteHistorial = null;
    }

    #[On('eliminarPaciente')]
    public function eliminarPaciente(int $id): void
    {
        $paciente = Paciente::findOrFail($id);

        // Validar si tiene proformas activas en curso
        $tieneProformasEnCurso = $paciente->proformas()->where('estado', 'En Curso')->exists();
        if ($tieneProformasEnCurso) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción Denegada',
                'text' => 'No se puede eliminar el paciente porque cuenta con proformas clínicas activas en curso.',
            ]);

            return;
        }

        $nombre = $paciente->nombre_completo;
        $paciente->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Paciente Eliminado',
            'text' => "El registro de {$nombre} ha sido eliminado.",
        ]);
    }

    public function render(): View
    {
        $query = Paciente::query()
            ->withCount(['proformas as proformas_activas_count' => function ($q) {
                $q->where('estado', 'En Curso');
            }])
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nombres', 'like', $term)
                        ->orWhere('apellido_paterno', 'like', $term)
                        ->orWhere('apellido_materno', 'like', $term)
                        ->orWhere('cedula', 'like', $term)
                        ->orWhere('celular', 'like', $term);
                });
            })
            ->when($this->filtroGenero !== '', function ($q) {
                $q->where('genero', $this->filtroGenero);
            })
            ->latest('id');

        $pacientes = $query->paginate($this->perPage);

        // Métricas rápidas
        $totalPacientes = Paciente::count();
        $pacientesConProformaActiva = Proforma::where('estado', 'En Curso')->distinct('paciente_id')->count('paciente_id');
        $pacientesNuevosMes = Paciente::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        return view('livewire.pacientes.pacientes-index', [
            'pacientes' => $pacientes,
            'totalPacientes' => $totalPacientes,
            'pacientesConProformaActiva' => $pacientesConProformaActiva,
            'pacientesNuevosMes' => $pacientesNuevosMes,
        ]);
    }
}
