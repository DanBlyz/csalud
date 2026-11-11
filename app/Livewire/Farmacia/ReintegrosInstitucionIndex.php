<?php

namespace App\Livewire\Farmacia;

use App\Models\ConsumoExtra;
use App\Models\Institucion;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\RecetaDetalle;
use App\Models\ReintegroInstitucion;
use App\Models\ReintegroInstitucionAplicacion;
use App\Models\ReintegroInstitucionDetalle;
use App\Models\Seccion;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReintegrosInstitucionIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda de la Tabla Principal
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroInstitucion = null;

    public ?int $filtroSucursal = null;

    public ?string $filtroFechaDesde = null;

    public ?string $filtroFechaHasta = null;

    // =========================================================================
    // MODAL: REGISTRAR NUEVO REINTEGRO POR INSTITUCIÓN
    // =========================================================================
    public bool $modalReintegroOpen = false;

    public ?int $institucion_id = null;

    public ?int $sucursal_id = null;

    public ?int $seccion_destino_id = null;

    public string $fecha_reintegro = '';

    public string $persona_entrega = '';

    public string $ci_persona_entrega = '';

    public string $observaciones = '';

    // Medicamentos usados por pacientes de la institución (en Proformas 'En Curso')
    public array $consumos_institucion = [];

    // Formulario para agregar un Lote de Reintegro a la cola
    public ?int $item_producto_id = null;

    public string $item_producto_nombre = '';

    public string $item_unidad_medida = '';

    public string $item_marca_nombre = '';

    public int $item_cantidad_usada_total = 0;

    public int $item_cantidad_disponible_reintegrar = 0;

    public int $item_cantidad_reintegrar = 1;

    public string $item_codigo_lote = '';

    public ?string $item_fecha_vencimiento = null;

    public string $item_precio_compra = '0.00';

    public string $item_precio_venta = '0.00';

    public string $item_observaciones = '';

    // Cola de lotes a ingresar en el acta de reintegro
    public array $cola_reintegro = [];

    // =========================================================================
    // MODAL: VER DETALLE DE REINTEGRO REALIZADO
    // =========================================================================
    public bool $modalDetalleOpen = false;

    public ?int $reintegroDetalleId = null;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.reintegros.ver') && ! $user->tienePermiso(9)) {
            abort(403, 'No cuenta con autorización para acceder al módulo de Reintegro por Institución.');
        }

        $this->sucursal_id = $user?->sucursal_id ?? Sucursal::where('estado', true)->first()?->id ?? Sucursal::first()?->id;
        $this->filtroSucursal = $user?->sucursal_id ?? null;
        $this->fecha_reintegro = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroInstitucion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaHasta(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroInstitucion = null;
        $this->filtroFechaDesde = null;
        $this->filtroFechaHasta = null;
        $this->resetPage();
    }

    // =========================================================================
    // GESTIÓN DEL MODAL DE NUEVO REINTEGRO
    // =========================================================================

    public function abrirModalReintegro(): void
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.reintegros.crear') && ! $user->tienePermiso(9)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para registrar reintegros por institución.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->institucion_id = null;
        $this->sucursal_id = Auth::user()?->sucursal_id ?? Sucursal::where('estado', true)->first()?->id ?? Sucursal::first()?->id;
        $this->seccion_destino_id = Seccion::where('sucursal_id', $this->sucursal_id)
            ->where('activo', true)
            ->where('es_almacen_principal', true)
            ->value('id') ?? Seccion::where('sucursal_id', $this->sucursal_id)->where('activo', true)->value('id');
        $this->fecha_reintegro = now()->toDateString();
        $this->persona_entrega = '';
        $this->ci_persona_entrega = '';
        $this->observaciones = '';
        $this->consumos_institucion = [];
        $this->cola_reintegro = [];
        $this->limpiarItemReintegro();

        $this->modalReintegroOpen = true;
    }

    public function cerrarModalReintegro(): void
    {
        $this->modalReintegroOpen = false;
        $this->consumos_institucion = [];
        $this->cola_reintegro = [];
        $this->limpiarItemReintegro();
        $this->resetValidation();
    }

    public function updatedSucursalId($val): void
    {
        if ($val) {
            $this->seccion_destino_id = Seccion::where('sucursal_id', (int) $val)
                ->where('activo', true)
                ->where('es_almacen_principal', true)
                ->value('id') ?? Seccion::where('sucursal_id', (int) $val)->where('activo', true)->value('id');
        }
        $this->cola_reintegro = [];
        $this->limpiarItemReintegro();
        $this->cargarConsumosInstitucion();
    }

    public function updatedInstitucionId($val): void
    {
        $this->cola_reintegro = [];
        $this->limpiarItemReintegro();
        $this->cargarConsumosInstitucion();
    }

    /**
     * Consulta todos los medicamentos e insumos usados por pacientes de la institución seleccionada
     * cuyas proformas estén únicamente en estado = 'En Curso', ordenadas de la más antigua a la más reciente.
     */
    public function cargarConsumosInstitucion(): void
    {
        $this->consumos_institucion = [];

        if (! $this->institucion_id) {
            return;
        }

        $proformasEnCurso = Proforma::with(['paciente'])
            ->where('estado', 'En Curso')
            ->when($this->sucursal_id, fn ($q) => $q->where('sucursal_id', $this->sucursal_id))
            ->whereHas('paciente', function (Builder $q) {
                $q->where('institucion_id', $this->institucion_id);
            })
            ->orderBy('fecha_ingreso', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($proformasEnCurso->isEmpty()) {
            return;
        }

        $proformaIds = $proformasEnCurso->pluck('id')->all();
        $proformasMap = $proformasEnCurso->keyBy('id');

        $movimientos = MovimientoInventario::with(['producto.marca', 'lote', 'devoluciones'])
            ->whereIn('proforma_id', $proformaIds)
            ->whereIn('tipo_movimiento', ['Salida Receta', 'Consumo Extra'])
            ->orderBy('id', 'asc')
            ->get();

        // Ordenar movimientos estrictamente por antigüedad de la proforma (fecha_ingreso ASC, proforma.id ASC, mov.id ASC)
        $movimientosOrdenados = $movimientos->sortBy(function (MovimientoInventario $mov) use ($proformasMap) {
            $prof = $proformasMap->get($mov->proforma_id);
            $timestamp = $prof?->fecha_ingreso ? $prof->fecha_ingreso->timestamp : ($prof?->created_at?->timestamp ?? 0);

            return sprintf('%012d-%010d-%010d', $timestamp, $mov->proforma_id, $mov->id);
        });

        $agrupados = [];

        foreach ($movimientosOrdenados as $mov) {
            $disponible = $mov->cantidadDisponibleParaDevolucion();
            if ($disponible <= 0) {
                continue;
            }

            $prodId = $mov->producto_id;
            $prod = $mov->producto;
            $prof = $proformasMap->get($mov->proforma_id);

            if (! $prod || ! $prof) {
                continue;
            }

            if (! isset($agrupados[$prodId])) {
                $ultimoLote = Lote::where('producto_id', $prodId)->latest('id')->first();
                $agrupados[$prodId] = [
                    'producto_id' => $prodId,
                    'producto_nombre' => $prod->nombre,
                    'unidad_medida' => $prod->unidad_medida ?? 'Unidad',
                    'marca_nombre' => $prod->marca?->nombre ?? 'Genérico',
                    'precio_compra_sugerido' => (float) ($ultimoLote?->precio_compra ?? 0),
                    'precio_venta_sugerido' => (float) ($prod->ultimo_precio_venta ?: ($ultimoLote?->precio_venta ?? 0)),
                    'cantidad_total_usada' => 0,
                    'desglose_proformas' => [],
                ];
            }

            $agrupados[$prodId]['cantidad_total_usada'] += $disponible;

            $profKey = $prof->id;
            if (! isset($agrupados[$prodId]['desglose_proformas'][$profKey])) {
                $agrupados[$prodId]['desglose_proformas'][$profKey] = [
                    'proforma_id' => $prof->id,
                    'fecha_ingreso' => $prof->fecha_ingreso ? $prof->fecha_ingreso->format('d/m/Y H:i') : ($prof->created_at?->format('d/m/Y H:i') ?? '-'),
                    'paciente_id' => $prof->paciente_id,
                    'paciente_nombre' => $prof->paciente?->nombre_completo ?? 'Paciente',
                    'paciente_cedula' => $prof->paciente?->cedula ?? 'S/C',
                    'cantidad_usada' => 0,
                ];
            }

            $agrupados[$prodId]['desglose_proformas'][$profKey]['cantidad_usada'] += $disponible;
        }

        // Convertir desglose_proformas a array indexado
        foreach ($agrupados as $prodId => &$datos) {
            $datos['desglose_proformas'] = array_values($datos['desglose_proformas']);
        }

        $this->consumos_institucion = $agrupados;
    }

    /**
     * Calcula cuántas unidades de un producto ya están en la cola temporal de reintegro.
     */
    public function cantidadEnColaPorProducto(int $productoId): int
    {
        $suma = 0;
        foreach ($this->cola_reintegro as $item) {
            if ((int) $item['producto_id'] === $productoId) {
                $suma += (int) $item['cantidad_reintegrada'];
            }
        }

        return $suma;
    }

    /**
     * Selecciona un medicamento de la lista de consumos de la institución para configurar su nuevo lote de reintegro.
     */
    public function seleccionarMedicamentoParaReintegro(int $productoId): void
    {
        if (! isset($this->consumos_institucion[$productoId])) {
            return;
        }

        $info = $this->consumos_institucion[$productoId];
        $enCola = $this->cantidadEnColaPorProducto($productoId);
        $disponible = max(0, (int) $info['cantidad_total_usada'] - $enCola);

        if ($disponible <= 0) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Totalmente en cola',
                'text' => 'Ya ha agregado la totalidad de unidades usadas de este medicamento a la lista de reintegro.',
            ]);

            return;
        }

        $this->item_producto_id = $productoId;
        $this->item_producto_nombre = $info['producto_nombre'];
        $this->item_unidad_medida = $info['unidad_medida'];
        $this->item_marca_nombre = $info['marca_nombre'];
        $this->item_cantidad_usada_total = (int) $info['cantidad_total_usada'];
        $this->item_cantidad_disponible_reintegrar = $disponible;
        $this->item_cantidad_reintegrar = $disponible;
        $this->item_codigo_lote = 'REINT-'.strtoupper(substr(uniqid(), -5));
        $this->item_fecha_vencimiento = now()->addYear()->toDateString();
        $this->item_precio_compra = number_format((float) $info['precio_compra_sugerido'], 2, '.', '');
        $this->item_precio_venta = number_format((float) $info['precio_venta_sugerido'], 2, '.', '');
        $this->item_observaciones = '';
        $this->resetValidation();
    }

    public function limpiarItemReintegro(): void
    {
        $this->item_producto_id = null;
        $this->item_producto_nombre = '';
        $this->item_unidad_medida = '';
        $this->item_marca_nombre = '';
        $this->item_cantidad_usada_total = 0;
        $this->item_cantidad_disponible_reintegrar = 0;
        $this->item_cantidad_reintegrar = 1;
        $this->item_codigo_lote = '';
        $this->item_fecha_vencimiento = null;
        $this->item_precio_compra = '0.00';
        $this->item_precio_venta = '0.00';
        $this->item_observaciones = '';
    }

    public function agregarItemReintegro(): void
    {
        if (! $this->item_producto_id || ! isset($this->consumos_institucion[$this->item_producto_id])) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un medicamento',
                'text' => 'Debe elegir uno de los medicamentos usados por los pacientes de la institución.',
            ]);

            return;
        }

        $info = $this->consumos_institucion[$this->item_producto_id];
        $enCola = $this->cantidadEnColaPorProducto($this->item_producto_id);
        $maxDisponible = max(0, (int) $info['cantidad_total_usada'] - $enCola);

        $this->validate([
            'item_cantidad_reintegrar' => ['required', 'integer', 'min:1', 'max:'.$maxDisponible],
            'item_codigo_lote' => ['required', 'string', 'max:100'],
            'item_fecha_vencimiento' => ['nullable', 'date'],
            'item_precio_compra' => ['required', 'numeric', 'min:0'],
            'item_precio_venta' => ['required', 'numeric', 'min:0'],
        ], [
            'item_cantidad_reintegrar.required' => 'Indique la cantidad a reintegrar.',
            'item_cantidad_reintegrar.min' => 'La cantidad mínima a reintegrar es 1 unidad.',
            'item_cantidad_reintegrar.max' => "La cantidad a reintegrar no puede superar el saldo usado pendiente ({$maxDisponible} unidades).",
            'item_codigo_lote.required' => 'El código del nuevo lote es obligatorio.',
            'item_precio_compra.required' => 'El precio de compra del lote es obligatorio.',
            'item_precio_venta.required' => 'El precio de venta del lote es obligatorio.',
        ]);

        // Simular distribución FIFO (desde la proforma más antigua) considerando lo que ya está en cola
        $yaDistribuidoPrevio = $enCola;
        $porDistribuirActual = (int) $this->item_cantidad_reintegrar;
        $simulacionPacientes = [];

        foreach ($info['desglose_proformas'] as $profData) {
            $usadoProf = (int) $profData['cantidad_usada'];

            if ($yaDistribuidoPrevio >= $usadoProf) {
                $yaDistribuidoPrevio -= $usadoProf;

                continue;
            }

            $disponibleEnEstaProforma = $usadoProf - $yaDistribuidoPrevio;
            $yaDistribuidoPrevio = 0;

            if ($porDistribuirActual <= 0) {
                break;
            }

            $descontarDeEsta = min($porDistribuirActual, $disponibleEnEstaProforma);
            $simulacionPacientes[] = [
                'proforma_id' => $profData['proforma_id'],
                'fecha_ingreso' => $profData['fecha_ingreso'],
                'paciente_nombre' => $profData['paciente_nombre'],
                'cantidad_descontada' => $descontarDeEsta,
            ];

            $porDistribuirActual -= $descontarDeEsta;
        }

        $this->cola_reintegro[] = [
            'producto_id' => $this->item_producto_id,
            'producto_nombre' => $this->item_producto_nombre,
            'unidad_medida' => $this->item_unidad_medida,
            'marca_nombre' => $this->item_marca_nombre,
            'cantidad_usada_institucion' => (int) $info['cantidad_total_usada'],
            'cantidad_reintegrada' => (int) $this->item_cantidad_reintegrar,
            'codigo_lote' => trim($this->item_codigo_lote),
            'fecha_vencimiento' => $this->item_fecha_vencimiento ?: null,
            'precio_compra' => (float) $this->item_precio_compra,
            'precio_venta' => (float) $this->item_precio_venta,
            'observaciones' => trim($this->item_observaciones),
            'simulacion_pacientes' => $simulacionPacientes,
        ];

        $this->limpiarItemReintegro();
    }

    public function eliminarItemCola(int $index): void
    {
        if (isset($this->cola_reintegro[$index])) {
            unset($this->cola_reintegro[$index]);
            $this->cola_reintegro = array_values($this->cola_reintegro);
        }
        if ($this->item_producto_id) {
            $this->limpiarItemReintegro();
        }
    }

    #[Computed]
    public function totalUnidadesCola(): int
    {
        return (int) array_sum(array_column($this->cola_reintegro, 'cantidad_reintegrada'));
    }

    #[Computed]
    public function totalMontoEstimadoDescuentoCola(): float
    {
        $total = 0.0;
        foreach ($this->cola_reintegro as $item) {
            $total += ((int) $item['cantidad_reintegrada']) * ((float) $item['precio_venta']);
        }

        return round($total, 2);
    }

    /**
     * Guarda el Acta de Reintegro por Institución:
     * 1. Crea los nuevos lotes en inventario y los asigna a la sección de farmacia.
     * 2. Descuenta los medicamentos usados de las proformas 'En Curso' de los pacientes de la institución,
     *    empezando estrictamente por la proforma más antigua (fecha_ingreso ASC, id ASC).
     * 3. Registra los asientos en Kardex (MovimientoInventario) y actualiza/elimina los ítems en RecetaDetalle o ConsumoExtra.
     * 4. Recalcula el costo_total de cada proforma afectada para disminuir el pago respectivo.
     */
    public function guardarReintegro(): void
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.reintegros.crear') && ! $user->tienePermiso(9)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para registrar reintegros por institución.',
            ]);

            return;
        }

        if ($this->item_producto_id && $this->item_cantidad_reintegrar > 0 && ! empty(trim($this->item_codigo_lote))) {
            $this->agregarItemReintegro();
        }

        $this->validate([
            'institucion_id' => ['required', 'exists:instituciones,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'seccion_destino_id' => ['required', 'exists:secciones,id'],
            'fecha_reintegro' => ['required', 'date'],
            'persona_entrega' => ['required', 'string', 'min:2', 'max:255'],
            'ci_persona_entrega' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'cola_reintegro' => ['required', 'array', 'min:1'],
        ], [
            'institucion_id.required' => 'Seleccione la institución o convenio.',
            'sucursal_id.required' => 'Seleccione la sucursal.',
            'seccion_destino_id.required' => 'Seleccione la sección de farmacia donde ingresarán los nuevos lotes.',
            'fecha_reintegro.required' => 'Indique la fecha del reintegro.',
            'persona_entrega.required' => 'Ingrese el nombre de la persona que realiza la entrega de los medicamentos.',
            'cola_reintegro.required' => 'Debe agregar al menos un medicamento o insumo a reintegrar.',
            'cola_reintegro.min' => 'Debe agregar al menos un medicamento o insumo a reintegrar.',
        ]);

        try {
            $reintegroCreado = DB::transaction(function () {
                $institucion = Institucion::findOrFail($this->institucion_id);
                $codigoActa = ReintegroInstitucion::generarSiguienteCodigo();

                $reintegro = ReintegroInstitucion::create([
                    'sucursal_id' => $this->sucursal_id,
                    'institucion_id' => $this->institucion_id,
                    'seccion_destino_id' => $this->seccion_destino_id,
                    'codigo' => $codigoActa,
                    'fecha_reintegro' => $this->fecha_reintegro,
                    'persona_entrega' => trim($this->persona_entrega),
                    'ci_persona_entrega' => ! empty(trim($this->ci_persona_entrega)) ? trim($this->ci_persona_entrega) : null,
                    'observaciones' => ! empty(trim($this->observaciones)) ? trim($this->observaciones) : null,
                    'total_unidades' => 0,
                    'monto_total_descontado' => 0,
                ]);

                $totalUnidadesGlobal = 0;
                $montoTotalDescontadoGlobal = 0.0;
                $proformasAfectadas = [];

                foreach ($this->cola_reintegro as $item) {
                    $cantReintegrada = (int) $item['cantidad_reintegrada'];
                    $productoId = (int) $item['producto_id'];
                    $producto = Producto::findOrFail($productoId);
                    $precioOriginalProducto = (float) ($producto->ultimo_precio_venta ?: $item['precio_venta']);

                    // 1. Obtener todas las proformas 'En Curso' de pacientes de esta institución ordenadas desde la más antigua
                    $proformasEnCurso = Proforma::with(['paciente'])
                        ->where('estado', 'En Curso')
                        ->where('sucursal_id', $this->sucursal_id)
                        ->whereHas('paciente', fn ($q) => $q->where('institucion_id', $this->institucion_id))
                        ->orderBy('fecha_ingreso', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $proformaIds = $proformasEnCurso->pluck('id')->all();
                    $proformasMap = $proformasEnCurso->keyBy('id');

                    $movimientosCandidatos = MovimientoInventario::with(['devoluciones'])
                        ->whereIn('proforma_id', $proformaIds)
                        ->where('producto_id', $productoId)
                        ->whereIn('tipo_movimiento', ['Salida Receta', 'Consumo Extra'])
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get()
                        ->sortBy(function (MovimientoInventario $mov) use ($proformasMap) {
                            $prof = $proformasMap->get($mov->proforma_id);
                            $timestamp = $prof?->fecha_ingreso ? $prof->fecha_ingreso->timestamp : ($prof?->created_at?->timestamp ?? 0);

                            return sprintf('%012d-%010d-%010d', $timestamp, $mov->proforma_id, $mov->id);
                        });

                    $totalDisponibleReal = $movimientosCandidatos->sum(fn (MovimientoInventario $m) => $m->cantidadDisponibleParaDevolucion());

                    if ($cantReintegrada > $totalDisponibleReal) {
                        throw new \DomainException("La cantidad a reintegrar de '{$producto->nombre}' ({$cantReintegrada}) supera la cantidad usada pendiente en proformas en curso ({$totalDisponibleReal}).");
                    }

                    // 2. Crear el Nuevo Lote en el inventario con su código y vencimiento propio
                    $nuevoLote = Lote::create([
                        'sucursal_id' => $this->sucursal_id,
                        'producto_id' => $productoId,
                        'proveedor_id' => null,
                        'codigo_lote' => trim($item['codigo_lote']),
                        'nro_documento' => $codigoActa,
                        'cantidad_ingresada' => $cantReintegrada,
                        'cantidad_actual' => $cantReintegrada,
                        'fecha_compra' => $this->fecha_reintegro,
                        'fecha_vencimiento' => $item['fecha_vencimiento'] ?: null,
                        'precio_compra' => (float) $item['precio_compra'],
                        'precio_venta' => (float) $item['precio_venta'],
                    ]);

                    // Sincronizar stock del nuevo lote en la sección receptora seleccionada
                    $loteSec = LoteSeccion::where('lote_id', $nuevoLote->id)->first();
                    if ($loteSec) {
                        $loteSec->update([
                            'seccion_id' => $this->seccion_destino_id,
                            'cantidad_actual' => $cantReintegrada,
                        ]);
                    } else {
                        LoteSeccion::create([
                            'lote_id' => $nuevoLote->id,
                            'seccion_id' => $this->seccion_destino_id,
                            'cantidad_actual' => $cantReintegrada,
                        ]);
                    }

                    // 3. Crear el Detalle del Reintegro
                    $detalleReintegro = ReintegroInstitucionDetalle::create([
                        'reintegro_institucion_id' => $reintegro->id,
                        'producto_id' => $productoId,
                        'lote_id' => $nuevoLote->id,
                        'seccion_id' => $this->seccion_destino_id,
                        'codigo_lote' => $nuevoLote->codigo_lote,
                        'fecha_vencimiento' => $nuevoLote->fecha_vencimiento,
                        'cantidad_usada_institucion' => (int) $item['cantidad_usada_institucion'],
                        'cantidad_reintegrada' => $cantReintegrada,
                        'precio_compra' => (float) $item['precio_compra'],
                        'precio_venta' => (float) $item['precio_venta'],
                        'observaciones' => $item['observaciones'] ?: null,
                    ]);

                    // 4. Descontar en orden de antigüedad de la proforma (FIFO: de la más antigua a la más reciente)
                    $restantePorDescontar = $cantReintegrada;

                    foreach ($movimientosCandidatos as $movOrigen) {
                        if ($restantePorDescontar <= 0) {
                            break;
                        }

                        $dispMov = $movOrigen->cantidadDisponibleParaDevolucion();
                        if ($dispMov <= 0) {
                            continue;
                        }

                        $cantAplicar = min($restantePorDescontar, $dispMov);
                        $proforma = $proformasMap->get($movOrigen->proforma_id);

                        if (! $proforma) {
                            continue;
                        }

                        $precioUnitarioAplicado = $precioOriginalProducto;

                        if ($movOrigen->tipo_movimiento === 'Salida Receta') {
                            // Actualizar o quitar de RecetaDetalle según la cantidad reintegrada
                            if ($movOrigen->receta_id) {
                                $recDetalle = RecetaDetalle::where('receta_id', $movOrigen->receta_id)
                                    ->where('producto_id', $productoId)
                                    ->first();

                                if ($recDetalle) {
                                    if ($recDetalle->costo_unitario > 0) {
                                        $precioUnitarioAplicado = (float) $recDetalle->costo_unitario;
                                    }
                                    if ($recDetalle->cantidad > $cantAplicar) {
                                        $recDetalle->decrement('cantidad', $cantAplicar);
                                    } else {
                                        $recDetalle->delete();
                                    }
                                }
                            }
                        } elseif ($movOrigen->tipo_movimiento === 'Consumo Extra') {
                            // Disminuir o quitar de ConsumoExtra en la proforma
                            $consumosExtras = ConsumoExtra::where('proforma_id', $proforma->id)
                                ->where('producto_id', $productoId)
                                ->orderBy('id', 'asc')
                                ->get();

                            $porQuitarConsumo = $cantAplicar;
                            foreach ($consumosExtras as $ce) {
                                if ($porQuitarConsumo <= 0) {
                                    break;
                                }

                                $precioUnitarioAplicado = (float) $ce->precio_unitario;

                                if ($ce->cantidad > $porQuitarConsumo) {
                                    $ce->decrement('cantidad', $porQuitarConsumo);
                                    $porQuitarConsumo = 0;
                                } else {
                                    $porQuitarConsumo -= $ce->cantidad;
                                    $ce->delete();
                                }
                            }
                        }

                        // Registrar el movimiento de Devolución / Reintegro en Kardex vinculado al movimiento origen y a la proforma
                        $obsKardex = "Reintegro por Institución ({$institucion->nombre}) - Acta {$codigoActa} - Nuevo Lote: {$nuevoLote->codigo_lote}";

                        $movDevolucion = MovimientoInventario::create([
                            'sucursal_id' => $this->sucursal_id,
                            'producto_id' => $productoId,
                            'lote_id' => $nuevoLote->id,
                            'cantidad' => $cantAplicar,
                            'tipo_movimiento' => 'Devolución',
                            'receta_id' => $movOrigen->tipo_movimiento === 'Salida Receta' ? $movOrigen->receta_id : null,
                            'proforma_id' => $proforma->id,
                            'movimiento_origen_id' => $movOrigen->id,
                            'seccion_origen_id' => $movOrigen->seccion_origen_id,
                            'seccion_destino_id' => $this->seccion_destino_id,
                            'user_id' => Auth::id() ?? 1,
                            'observaciones' => $obsKardex,
                        ]);

                        $subtotalDescontado = round($cantAplicar * $precioUnitarioAplicado, 2);

                        // Registrar la aplicación exacta por paciente/proforma
                        ReintegroInstitucionAplicacion::create([
                            'reintegro_institucion_id' => $reintegro->id,
                            'reintegro_institucion_detalle_id' => $detalleReintegro->id,
                            'proforma_id' => $proforma->id,
                            'paciente_id' => $proforma->paciente_id,
                            'producto_id' => $productoId,
                            'movimiento_origen_id' => $movOrigen->id,
                            'movimiento_devolucion_id' => $movDevolucion->id,
                            'tipo_origen' => $movOrigen->tipo_movimiento,
                            'cantidad_descontada' => $cantAplicar,
                            'precio_unitario' => $precioUnitarioAplicado,
                            'subtotal_descontado' => $subtotalDescontado,
                        ]);

                        $restantePorDescontar -= $cantAplicar;
                        $totalUnidadesGlobal += $cantAplicar;
                        $montoTotalDescontadoGlobal += $subtotalDescontado;
                        $proformasAfectadas[$proforma->id] = $proforma;
                    }
                }

                // 5. Recalcular el costo total de todas las proformas afectadas
                foreach ($proformasAfectadas as $profAfectada) {
                    $profAfectada->recalcularTotal();
                }

                // 6. Actualizar totales en la cabecera del acta de reintegro
                $reintegro->update([
                    'total_unidades' => $totalUnidadesGlobal,
                    'monto_total_descontado' => round($montoTotalDescontadoGlobal, 2),
                ]);

                return $reintegro;
            });

            $this->cerrarModalReintegro();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Reintegro Registrado!',
                'text' => "Acta {$reintegroCreado->codigo} procesada: se ingresaron los nuevos lotes y se descontaron Bs. ".number_format((float) $reintegroCreado->monto_total_descontado, 2).' en las proformas en curso.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al procesar reintegro',
                'text' => $e->getMessage(),
                'toast' => false,
            ]);
        }
    }

    // =========================================================================
    // MODAL DE VISUALIZACIÓN DE DETALLE
    // =========================================================================

    public function verDetalle(int $reintegroId): void
    {
        $this->reintegroDetalleId = $reintegroId;
        $this->modalDetalleOpen = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalleOpen = false;
        $this->reintegroDetalleId = null;
    }

    public function render(): View
    {
        $query = ReintegroInstitucion::query()
            ->with([
                'institucion',
                'sucursal',
                'seccionDestino',
                'usuarioCreador',
                'detalles.producto.marca',
                'detalles.lote',
                'aplicaciones.paciente',
                'aplicaciones.proforma',
                'aplicaciones.producto',
            ]);

        if (! empty($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('codigo', 'like', $term)
                    ->orWhere('persona_entrega', 'like', $term)
                    ->orWhere('ci_persona_entrega', 'like', $term)
                    ->orWhereHas('institucion', fn (Builder $iq) => $iq->where('nombre', 'like', $term))
                    ->orWhereHas('detalles.producto', fn (Builder $pq) => $pq->where('nombre', 'like', $term))
                    ->orWhereHas('detalles', fn (Builder $dq) => $dq->where('codigo_lote', 'like', $term))
                    ->orWhereHas('aplicaciones.paciente', function (Builder $pac) use ($term) {
                        $pac->where('nombres', 'like', $term)
                            ->orWhere('apellido_paterno', 'like', $term)
                            ->orWhere('apellido_materno', 'like', $term)
                            ->orWhere('cedula', 'like', $term);
                    });
            });
        }

        if ($this->filtroInstitucion) {
            $query->where('institucion_id', $this->filtroInstitucion);
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if (! empty($this->filtroFechaDesde)) {
            $query->whereDate('fecha_reintegro', '>=', $this->filtroFechaDesde);
        }

        if (! empty($this->filtroFechaHasta)) {
            $query->whereDate('fecha_reintegro', '<=', $this->filtroFechaHasta);
        }

        $reintegros = $query->latest('id')->paginate($this->perPage);

        // Métricas generales
        $totalActas = ReintegroInstitucion::count();
        $totalUnidadesReintegradas = (int) ReintegroInstitucion::sum('total_unidades');
        $totalMontoDescontado = (float) ReintegroInstitucion::sum('monto_total_descontado');

        // Instituciones y Sucursales
        $instituciones = Institucion::where('estado', 'Activo')->orderBy('nombre')->get();
        $sucursales = Sucursal::where('estado', true)->orderBy('nombre')->get();

        $sucursalActiva = $this->sucursal_id ?? Auth::user()?->sucursal_id;
        $secciones = Seccion::where('activo', true)
            ->when($sucursalActiva, fn ($q) => $q->where('sucursal_id', $sucursalActiva))
            ->orderByDesc('es_almacen_principal')
            ->orderBy('nombre')
            ->get();

        $reintegroSeleccionado = $this->reintegroDetalleId
            ? ReintegroInstitucion::with([
                'institucion',
                'sucursal',
                'seccionDestino',
                'usuarioCreador',
                'detalles.producto.marca',
                'detalles.lote',
                'detalles.seccion',
                'aplicaciones.paciente',
                'aplicaciones.proforma',
                'aplicaciones.producto',
            ])->find($this->reintegroDetalleId)
            : null;

        return view('livewire.farmacia.reintegros-institucion-index', [
            'reintegros' => $reintegros,
            'totalActas' => $totalActas,
            'totalUnidadesReintegradas' => $totalUnidadesReintegradas,
            'totalMontoDescontado' => $totalMontoDescontado,
            'instituciones' => $instituciones,
            'sucursales' => $sucursales,
            'secciones' => $secciones,
            'reintegroSeleccionado' => $reintegroSeleccionado,
        ]);
    }
}
