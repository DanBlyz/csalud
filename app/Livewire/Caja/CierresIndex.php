<?php

namespace App\Livewire\Caja;

use App\Models\CierreDetalle;
use App\Models\CierreMensual;
use App\Models\Lote;
use App\Models\ProformaPago;
use App\Models\ProformaPagoMedico;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CierresIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroAnio = null;

    public string $filtroEstado = '';

    public ?int $filtroSucursal = null;

    // Modal Crear / Aperturar Cierre
    public bool $modalCrearOpen = false;

    public int $nuevo_anio = 2026;

    public int $nuevo_mes = 9;

    public string $nuevo_fecha_inicio = '';

    public string $nuevo_fecha_fin = '';

    public ?int $nuevo_sucursal_id = null;

    public string $nuevo_observaciones = '';

    // Modal Detalle / Gestión de Partidas del Cierre
    public bool $modalDetalleOpen = false;

    public ?int $cierreSeleccionadoId = null;

    public string $tabDetalle = 'resumen'; // resumen, ingresos, egresos_clinicos, egresos_operativos

    // Modal Agregar / Editar Partida (Gasto o Ingreso Extra)
    public bool $modalPartidaOpen = false;

    public ?int $editando_partida_id = null;

    public string $partida_tipo = 'Egreso'; // Ingreso, Egreso

    public string $partida_categoria = 'Servicio Básico'; // Cobro Proforma, Honorario Médico, Compra Farmacia, Servicio Básico, Sueldo, Gasto Operativo, Otro

    public string $partida_concepto = '';

    public string $partida_monto = '';

    public string $partida_fecha = '';

    public string $partida_referencia = '';

    public string $partida_observaciones = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroAnio' => ['except' => null],
        'filtroEstado' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->filtroAnio = (int) date('Y');
        $this->filtroSucursal = Auth::user()->sucursal_id;

        $this->nuevo_anio = (int) date('Y');
        $this->nuevo_mes = (int) date('n');
        $this->nuevo_fecha_inicio = now()->startOfMonth()->toDateString();
        $this->nuevo_fecha_fin = now()->endOfMonth()->toDateString();
        $this->nuevo_sucursal_id = Auth::user()->sucursal_id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroAnio(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    // =========================================================================
    // 1. APERTURA DE NUEVO CIERRE MENSUAL
    // =========================================================================
    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->nuevo_anio = (int) date('Y');
        $this->nuevo_mes = (int) date('n');
        $this->nuevo_fecha_inicio = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->startOfMonth()->toDateString();
        $this->nuevo_fecha_fin = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->endOfMonth()->toDateString();
        $this->nuevo_sucursal_id = Auth::user()->sucursal_id;
        $this->nuevo_observaciones = '';
        $this->modalCrearOpen = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->modalCrearOpen = false;
        $this->resetValidation();
    }

    public function updatedNuevoMes(): void
    {
        if ($this->nuevo_mes >= 1 && $this->nuevo_mes <= 12 && $this->nuevo_anio > 2000) {
            $this->nuevo_fecha_inicio = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->startOfMonth()->toDateString();
            $this->nuevo_fecha_fin = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->endOfMonth()->toDateString();
        }
    }

    public function updatedNuevoAnio(): void
    {
        if ($this->nuevo_mes >= 1 && $this->nuevo_mes <= 12 && $this->nuevo_anio > 2000) {
            $this->nuevo_fecha_inicio = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->startOfMonth()->toDateString();
            $this->nuevo_fecha_fin = Carbon::create($this->nuevo_anio, $this->nuevo_mes, 1)->endOfMonth()->toDateString();
        }
    }

    public function crearCierre(): void
    {
        $this->validate([
            'nuevo_anio' => ['required', 'integer', 'min:2020', 'max:2035'],
            'nuevo_mes' => ['required', 'integer', 'between:1,12'],
            'nuevo_fecha_inicio' => ['required', 'date'],
            'nuevo_fecha_fin' => ['required', 'date', 'after_or_equal:nuevo_fecha_inicio'],
            'nuevo_sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'nuevo_observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'nuevo_anio.required' => 'El año fiscal es obligatorio.',
            'nuevo_mes.required' => 'El mes del cierre es obligatorio.',
            'nuevo_fecha_inicio.required' => 'La fecha de inicio es requerida.',
            'nuevo_fecha_fin.required' => 'La fecha de fin es requerida.',
        ]);

        $sucursalId = $this->nuevo_sucursal_id ?? Auth::user()->sucursal_id;

        // Validar unicidad por sucursal, año y mes
        $existe = CierreMensual::where('sucursal_id', $sucursalId)
            ->where('anio', $this->nuevo_anio)
            ->where('mes', $this->nuevo_mes)
            ->first();

        if ($existe) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cierre Existente',
                'text' => "Ya existe un cierre registrado para {$existe->nombre_mes} {$existe->anio} en esta sucursal.",
            ]);

            return;
        }

        DB::beginTransaction();
        try {
            $cierre = CierreMensual::create([
                'sucursal_id' => $sucursalId,
                'anio' => $this->nuevo_anio,
                'mes' => $this->nuevo_mes,
                'fecha_inicio' => $this->nuevo_fecha_inicio,
                'fecha_fin' => $this->nuevo_fecha_fin,
                'total_ingresos' => 0.00,
                'total_egresos' => 0.00,
                'utilidad_neta' => 0.00,
                'estado' => 'Borrador',
                'observaciones' => $this->nuevo_observaciones ? trim($this->nuevo_observaciones) : null,
                'user_id' => Auth::id(),
            ]);

            // Importar movimientos automáticos del período
            $this->importarMovimientosAutomaticos($cierre);

            $cierre->recalcularTotales();

            DB::commit();

            $this->cerrarModalCrear();
            $this->verCierre($cierre->id);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Cierre Mensual Creado',
                'text' => "Se consolidó el balance para {$cierre->nombre_mes} {$cierre->anio} con recaudación e ingresos/egresos calculados.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al Crear Cierre',
                'text' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Importa automáticamente cobros de proforma, honorarios médicos y compras de lotes del período.
     */
    protected function importarMovimientosAutomaticos(CierreMensual $cierre): void
    {
        $inicio = Carbon::parse($cierre->fecha_inicio)->startOfDay();
        $fin = Carbon::parse($cierre->fecha_fin)->endOfDay();
        $sucursalId = $cierre->sucursal_id;

        // 1. INGRESOS: Cobros de Caja (ProformaPago agrupados por método y día)
        $pagosQuery = ProformaPago::whereBetween('created_at', [$inicio, $fin]);
        if ($sucursalId) {
            $pagosQuery->whereHas('proforma', fn ($q) => $q->where('sucursal_id', $sucursalId));
        }

        $pagos = $pagosQuery->with(['proforma.paciente', 'user'])->get();

        foreach ($pagos as $pago) {
            $pacienteNombre = $pago->proforma?->paciente?->nombre_completo ?? 'Paciente';
            $refText = $pago->numero_referencia ? " [Ref: {$pago->numero_referencia}]" : '';

            CierreDetalle::create([
                'cierre_mensual_id' => $cierre->id,
                'tipo' => 'Ingreso',
                'categoria' => 'Cobro Proforma',
                'concepto' => "Cobro Proforma #{$pago->proforma_id} ({$pacienteNombre}) - {$pago->tipo_pago}{$refText}",
                'monto' => (float) $pago->monto,
                'fecha' => $pago->created_at->toDateString(),
                'comprobante_referencia' => $pago->numero_referencia,
                'origen_tipo' => ProformaPago::class,
                'origen_id' => $pago->id,
                'user_id' => Auth::id(),
            ]);
        }

        // 2. EGRESOS CLÍNICOS: Honorarios Médicos (ProformaPagoMedico)
        $honorariosQuery = ProformaPagoMedico::whereBetween('fecha_pago', [$cierre->fecha_inicio, $cierre->fecha_fin]);
        if ($sucursalId) {
            $honorariosQuery->whereHas('proforma', fn ($q) => $q->where('sucursal_id', $sucursalId));
        }

        $honorarios = $honorariosQuery->with(['medico.especialidad', 'proforma.paciente'])->get();

        foreach ($honorarios as $hon) {
            $medicoNombre = $hon->medico?->nombre_completo ?? 'Médico';
            $esp = $hon->medico?->especialidad?->nombre ? " ({$hon->medico->especialidad->nombre})" : '';
            $obs = $hon->observaciones ? " - {$hon->observaciones}" : '';

            CierreDetalle::create([
                'cierre_mensual_id' => $cierre->id,
                'tipo' => 'Egreso',
                'categoria' => 'Honorario Médico',
                'concepto' => "Honorario Dr(a). {$medicoNombre}{$esp} - Proforma #{$hon->proforma_id}{$obs}",
                'monto' => (float) $hon->monto,
                'fecha' => $hon->fecha_pago->toDateString(),
                'origen_tipo' => ProformaPagoMedico::class,
                'origen_id' => $hon->id,
                'user_id' => Auth::id(),
            ]);
        }

        // 3. EGRESOS CLÍNICOS: Compras y Adquisición de Lotes (Lote)
        $lotesQuery = Lote::whereBetween('created_at', [$inicio, $fin]);
        if ($sucursalId) {
            $lotesQuery->where('sucursal_id', $sucursalId);
        }

        $lotes = $lotesQuery->with(['producto', 'proveedor'])->get();

        foreach ($lotes as $lote) {
            $costoTotalLote = (float) ($lote->cantidad_ingresada * $lote->precio_compra);
            if ($costoTotalLote <= 0) {
                continue;
            }

            $prodNombre = $lote->producto?->nombre ?? 'Medicamento';
            $provNombre = $lote->proveedor?->razon_social ? " [Prov: {$lote->proveedor->razon_social}]" : '';

            CierreDetalle::create([
                'cierre_mensual_id' => $cierre->id,
                'tipo' => 'Egreso',
                'categoria' => 'Compra Farmacia',
                'concepto' => "Compra Lote {$lote->codigo_lote}: {$prodNombre} ({$lote->cantidad_ingresada} unids x Bs. {$lote->precio_compra}){$provNombre}",
                'monto' => $costoTotalLote,
                'fecha' => $lote->created_at->toDateString(),
                'comprobante_referencia' => $lote->codigo_lote,
                'origen_tipo' => Lote::class,
                'origen_id' => $lote->id,
                'user_id' => Auth::id(),
            ]);
        }
    }

    // =========================================================================
    // 2. GESTIÓN Y REVISIÓN DE DETALLE DE CIERRE
    // =========================================================================
    public function verCierre(int $id): void
    {
        $this->cierreSeleccionadoId = $id;
        $this->tabDetalle = 'resumen';
        $this->modalDetalleOpen = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalleOpen = false;
        $this->cierreSeleccionadoId = null;
    }

    public function cambiarTabDetalle(string $tab): void
    {
        $this->tabDetalle = $tab;
    }

    public function sincronizarAutomaticos(): void
    {
        if (! $this->cierreSeleccionadoId) {
            return;
        }

        $cierre = CierreMensual::findOrFail($this->cierreSeleccionadoId);

        if ($cierre->estado === 'Cerrado') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cierre Finalizado',
                'text' => 'No se pueden sincronizar movimientos en un cierre que ya fue cerrado definitivamente.',
            ]);

            return;
        }

        // Eliminar solo los automáticos previamente importados para no duplicar
        $cierre->detalles()->whereNotNull('origen_tipo')->delete();

        $this->importarMovimientosAutomaticos($cierre);
        $cierre->recalcularTotales();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Datos Actualizados',
            'text' => 'Se sincronizaron los cobros en caja, honorarios médicos y compras de lotes del período.',
        ]);
    }

    public function cambiarEstadoCierre(string $nuevoEstado): void
    {
        if (! $this->cierreSeleccionadoId) {
            return;
        }

        $cierre = CierreMensual::findOrFail($this->cierreSeleccionadoId);
        $cierre->update(['estado' => $nuevoEstado]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Actualizado',
            'text' => "El cierre mensual ahora está en estado '{$nuevoEstado}'.",
        ]);
    }

    public function eliminarCierre(int $cierreId): void
    {
        $cierre = CierreMensual::findOrFail($cierreId);
        $cierre->delete();

        if ($this->cierreSeleccionadoId === $cierreId) {
            $this->cerrarModalDetalle();
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Cierre Eliminado',
            'text' => 'El cierre mensual y sus detalles han sido removidos.',
        ]);
    }

    // =========================================================================
    // 3. AGREGAR / EDITAR PARTIDA MANUAL (SERVICIOS BÁSICOS, SUELDOS, ETC.)
    // =========================================================================
    public function abrirModalPartida(string $tipo = 'Egreso'): void
    {
        $this->resetValidation();
        $this->editando_partida_id = null;
        $this->partida_tipo = $tipo;
        $this->partida_categoria = $tipo === 'Ingreso' ? 'Otro' : 'Servicio Básico';
        $this->partida_concepto = '';
        $this->partida_monto = '';
        $this->partida_fecha = now()->toDateString();
        $this->partida_referencia = '';
        $this->partida_observaciones = '';
        $this->modalPartidaOpen = true;
    }

    public function cerrarModalPartida(): void
    {
        $this->modalPartidaOpen = false;
        $this->resetValidation();
        $this->reset([
            'editando_partida_id',
            'partida_concepto',
            'partida_monto',
            'partida_fecha',
            'partida_referencia',
            'partida_observaciones',
        ]);
    }

    public function editarPartida(int $partidaId): void
    {
        $this->resetValidation();
        $partida = CierreDetalle::findOrFail($partidaId);

        $this->editando_partida_id = $partida->id;
        $this->partida_tipo = $partida->tipo;
        $this->partida_categoria = $partida->categoria;
        $this->partida_concepto = $partida->concepto;
        $this->partida_monto = number_format((float) $partida->monto, 2, '.', '');
        $this->partida_fecha = $partida->fecha ? $partida->fecha->toDateString() : now()->toDateString();
        $this->partida_referencia = $partida->comprobante_referencia ?? '';
        $this->partida_observaciones = $partida->observaciones ?? '';

        $this->modalPartidaOpen = true;
    }

    public function guardarPartida(): void
    {
        if (! $this->cierreSeleccionadoId) {
            return;
        }

        $cierre = CierreMensual::findOrFail($this->cierreSeleccionadoId);

        if ($cierre->estado === 'Cerrado') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cierre Finalizado',
                'text' => 'No se pueden modificar partidas en un cierre que ya fue cerrado definitivamente.',
            ]);

            return;
        }

        $this->validate([
            'partida_tipo' => ['required', 'in:Ingreso,Egreso'],
            'partida_categoria' => ['required', 'string', 'max:50'],
            'partida_concepto' => ['required', 'string', 'max:255'],
            'partida_monto' => ['required', 'numeric', 'min:0.01'],
            'partida_fecha' => ['required', 'date'],
            'partida_referencia' => ['nullable', 'string', 'max:100'],
            'partida_observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'partida_concepto.required' => 'El concepto o descripción es obligatorio.',
            'partida_monto.required' => 'El monto es obligatorio.',
            'partida_monto.min' => 'El monto debe ser mayor a 0.',
            'partida_fecha.required' => 'La fecha de la partida es obligatoria.',
        ]);

        if ($this->editando_partida_id) {
            $partida = CierreDetalle::where('cierre_mensual_id', $cierre->id)->findOrFail($this->editando_partida_id);
            $partida->update([
                'tipo' => $this->partida_tipo,
                'categoria' => $this->partida_categoria,
                'concepto' => trim($this->partida_concepto),
                'monto' => (float) $this->partida_monto,
                'fecha' => $this->partida_fecha,
                'comprobante_referencia' => $this->partida_referencia ? trim($this->partida_referencia) : null,
                'observaciones' => $this->partida_observaciones ? trim($this->partida_observaciones) : null,
            ]);

            $mensaje = 'La partida fue actualizada correctamente.';
        } else {
            CierreDetalle::create([
                'cierre_mensual_id' => $cierre->id,
                'tipo' => $this->partida_tipo,
                'categoria' => $this->partida_categoria,
                'concepto' => trim($this->partida_concepto),
                'monto' => (float) $this->partida_monto,
                'fecha' => $this->partida_fecha,
                'comprobante_referencia' => $this->partida_referencia ? trim($this->partida_referencia) : null,
                'observaciones' => $this->partida_observaciones ? trim($this->partida_observaciones) : null,
                'user_id' => Auth::id(),
            ]);

            $mensaje = 'La partida fue añadida al balance.';
        }

        $cierre->recalcularTotales();
        $this->cerrarModalPartida();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Partida Guardada',
            'text' => $mensaje,
        ]);
    }

    public function eliminarPartida(int $partidaId): void
    {
        if (! $this->cierreSeleccionadoId) {
            return;
        }

        $cierre = CierreMensual::findOrFail($this->cierreSeleccionadoId);

        if ($cierre->estado === 'Cerrado') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cierre Finalizado',
                'text' => 'No se pueden eliminar partidas en un cierre que ya fue cerrado definitivamente.',
            ]);

            return;
        }

        $partida = CierreDetalle::where('cierre_mensual_id', $cierre->id)->findOrFail($partidaId);
        $partida->delete();

        $cierre->recalcularTotales();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Partida Eliminada',
            'text' => 'La partida fue removida del balance y se recalcularon los totales.',
        ]);
    }

    // =========================================================================
    // 4. RENDERIZADO
    // =========================================================================
    public function render(): View
    {
        $query = CierreMensual::query()
            ->with(['sucursal', 'user'])
            ->withCount(['detalles', 'ingresos', 'egresos']);

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroAnio) {
            $query->where('anio', $this->filtroAnio);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if (! empty($this->search)) {
            $search = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->where('observaciones', 'like', $search)
                    ->orWhereHas('sucursal', fn ($s) => $s->where('nombre', 'like', $search))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search));
            });
        }

        $cierres = $query->orderBy('anio', 'desc')->orderBy('mes', 'desc')->paginate($this->perPage);

        // Métricas superiores rápidas
        $sucursalId = $this->filtroSucursal;
        $baseMetricas = CierreMensual::query();
        if ($sucursalId) {
            $baseMetricas->where('sucursal_id', $sucursalId);
        }

        $totalCierres = (clone $baseMetricas)->count();
        $totalCierresCerrados = (clone $baseMetricas)->where('estado', 'Cerrado')->count();
        $utilidadAcumulada = (float) (clone $baseMetricas)->where('estado', 'Cerrado')->sum('utilidad_neta');

        $sucursales = Sucursal::orderBy('nombre')->get();

        // Cierre actualmente seleccionado para modal de detalle
        $cierreSeleccionado = null;
        if ($this->cierreSeleccionadoId) {
            $cierreSeleccionado = CierreMensual::with([
                'sucursal',
                'user',
                'detalles' => fn ($q) => $q->orderBy('fecha', 'desc')->orderBy('id', 'desc'),
            ])->find($this->cierreSeleccionadoId);
        }

        return view('livewire.caja.cierres-index', [
            'cierres' => $cierres,
            'totalCierres' => $totalCierres,
            'totalCierresCerrados' => $totalCierresCerrados,
            'utilidadAcumulada' => $utilidadAcumulada,
            'sucursales' => $sucursales,
            'cierreSeleccionado' => $cierreSeleccionado,
        ]);
    }
}
