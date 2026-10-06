<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Seccion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteMovimientosService
{
    /**
     * Determina si un tipo de movimiento corresponde a una entrada física de inventario.
     */
    public static function esEntrada(string $tipo): bool
    {
        $tipoLower = mb_strtolower($tipo);

        return str_starts_with($tipoLower, 'entrada') ||
            str_contains($tipoLower, 'positivo') ||
            str_contains($tipoLower, 'ingreso');
    }

    /**
     * Genera la estructura completa de datos del Kardex de Movimientos y Valorización de Stock.
     *
     * @return array{
     *     fecha_inicio: string,
     *     fecha_fin: string,
     *     sucursal_id: ?int,
     *     producto_id: ?int,
     *     items: array<int, array{
     *         producto_id: int,
     *         producto_nombre: string,
     *         marca_nombre: string,
     *         unidad_medida: string,
     *         stock_minimo: int,
     *         saldo_inicial: int,
     *         movimientos: array<int, array{
     *             id: int,
     *             fecha: string,
     *             tipo_movimiento: string,
     *             es_entrada: bool,
     *             lote_codigo: string,
     *             lote_vencimiento: ?string,
     *             seccion_nombre: string,
     *             seccion_origen: ?string,
     *             seccion_destino: ?string,
     *             cantidad_entrada: int,
     *             cantidad_salida: int,
     *             saldo_acumulado: int,
     *             referencia: string,
     *             usuario: string
     *         }>,
     *         total_entradas: int,
     *         total_salidas: int,
     *         saldo_final: int,
     *         secciones_stock: array<int, array{
     *             seccion_id: ?int,
     *             seccion_nombre: string,
     *             es_principal: bool,
     *             cantidad: int
     *         }>,
     *         total_stock_secciones: int,
     *         ultimo_precio_compra: float,
     *         ultimo_precio_venta: float,
     *         valor_total_compra: float,
     *         valor_total_venta: float
     *     }>,
     *     resumen_general: array{
     *         total_productos: int,
     *         gran_total_saldo_inicial: int,
     *         gran_total_entradas: int,
     *         gran_total_salidas: int,
     *         gran_total_saldo_final: int,
     *         gran_total_valor_compra: float,
     *         gran_total_valor_venta: float,
     *         margen_bruto_potencial: float,
     *         stock_por_seccion: array<string, int>
     *     }
     * }
     */
    public function generar(
        string $fechaInicio,
        string $fechaFin,
        ?int $productoId = null,
        ?int $sucursalId = null,
        bool $soloConActividad = true
    ): array {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        // 1. Obtener productos a evaluar
        $productosQuery = Producto::query()
            ->with(['marca'])
            ->orderBy('nombre', 'asc');

        if ($productoId) {
            $productosQuery->where('id', $productoId);
        }

        $productos = $productosQuery->get();

        $items = [];
        $granTotalSaldoInicial = 0;
        $granTotalEntradas = 0;
        $granTotalSalidas = 0;
        $granTotalSaldoFinal = 0;
        $granTotalValorCompra = 0.0;
        $granTotalValorVenta = 0.0;
        $granTotalPorSeccion = [];

        // Obtener catálogo de secciones activas de la sucursal (o todas si sucursalId es null)
        $seccionesList = Seccion::query()
            ->where('activo', true)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre', 'asc')
            ->get();

        foreach ($seccionesList as $sec) {
            $granTotalPorSeccion[$sec->nombre] = 0;
        }

        foreach ($productos as $prod) {
            // 2. Saldo Inicial: Movimientos previos a la fecha_inicio
            $movsPrevios = MovimientoInventario::query()
                ->where('producto_id', $prod->id)
                ->where('created_at', '<', $inicio)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->get(['tipo_movimiento', 'cantidad']);

            $entradasPrevias = 0;
            $salidasPrevias = 0;
            foreach ($movsPrevios as $mp) {
                if (self::esEntrada($mp->tipo_movimiento)) {
                    $entradasPrevias += $mp->cantidad;
                } else {
                    $salidasPrevias += $mp->cantidad;
                }
            }
            $saldoInicial = $entradasPrevias - $salidasPrevias;

            // 3. Movimientos del período
            $movimientosPeriodo = MovimientoInventario::query()
                ->where('producto_id', $prod->id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->with(['lote', 'user', 'proforma.paciente', 'receta', 'seccionOrigen', 'seccionDestino'])
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Si se solicitó filtrar solo productos con actividad y no tiene saldo ni movimientos, omitir
            if ($soloConActividad && ! $productoId && $saldoInicial === 0 && $movimientosPeriodo->isEmpty()) {
                continue;
            }

            $saldoCorriente = $saldoInicial;
            $movimientosFormateados = [];
            $totalEntradasPeriodo = 0;
            $totalSalidasPeriodo = 0;

            foreach ($movimientosPeriodo as $m) {
                $esEntrada = self::esEntrada($m->tipo_movimiento);
                $cantEntrada = $esEntrada ? (int) $m->cantidad : 0;
                $cantSalida = ! $esEntrada ? (int) $m->cantidad : 0;

                $saldoCorriente += ($cantEntrada - $cantSalida);
                $totalEntradasPeriodo += $cantEntrada;
                $totalSalidasPeriodo += $cantSalida;

                $referencia = '-';
                if ($m->proforma) {
                    $paciente = $m->proforma->paciente?->nombre_completo ?? 'Paciente';
                    $referencia = "Proforma #{$m->proforma->id} ({$paciente})";
                } elseif ($m->receta) {
                    $referencia = "Receta #{$m->receta->id}";
                }

                $seccionNombre = '-';
                if ($m->tipo_movimiento === 'Transferencia Interna') {
                    $origen = $m->seccionOrigen?->nombre ?? 'Origen';
                    $destino = $m->seccionDestino?->nombre ?? 'Destino';
                    $seccionNombre = "{$origen} > {$destino}";
                } elseif (! $esEntrada) {
                    $seccionNombre = $m->seccionOrigen?->nombre ?? 'Farmacia Central';
                } else {
                    $seccionNombre = $m->seccionDestino?->nombre ?? ($m->seccionOrigen?->nombre ?? 'Farmacia Central');
                }

                $movimientosFormateados[] = [
                    'id' => $m->id,
                    'fecha' => $m->created_at->format('d/m/Y H:i'),
                    'tipo_movimiento' => $m->tipo_movimiento,
                    'es_entrada' => $esEntrada,
                    'lote_codigo' => $m->lote?->codigo_lote ?? 'Sin Lote',
                    'lote_vencimiento' => $m->lote?->fecha_vencimiento ? $m->lote->fecha_vencimiento->format('d/m/Y') : null,
                    'seccion_nombre' => $seccionNombre,
                    'seccion_origen' => $m->seccionOrigen?->nombre,
                    'seccion_destino' => $m->seccionDestino?->nombre,
                    'cantidad_entrada' => $cantEntrada,
                    'cantidad_salida' => $cantSalida,
                    'saldo_acumulado' => $saldoCorriente,
                    'referencia' => $referencia,
                    'usuario' => $m->user?->name ?? 'Sistema',
                ];
            }

            $saldoFinal = $saldoInicial + $totalEntradasPeriodo - $totalSalidasPeriodo;

            // 4. Últimos Precios de Compra y Venta del producto al corte
            $ultimoLote = Lote::query()
                ->where('producto_id', $prod->id)
                ->where('created_at', '<=', $fin)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->latest('id')
                ->first();

            if (! $ultimoLote) {
                $ultimoLote = Lote::query()
                    ->where('producto_id', $prod->id)
                    ->latest('id')
                    ->first();
            }

            $ultimoPrecioCompra = $ultimoLote ? (float) $ultimoLote->precio_compra : 0.0;
            $ultimoPrecioVenta = (float) ($prod->ultimo_precio_venta ?? 0);
            if ($ultimoPrecioVenta <= 0 && $ultimoLote) {
                $ultimoPrecioVenta = (float) $ultimoLote->precio_venta;
            }

            $valorCompra = max(0, $saldoFinal) * $ultimoPrecioCompra;
            $valorVenta = max(0, $saldoFinal) * $ultimoPrecioVenta;

            // 5. Desglose de existencias físicas por Sección Hospitalaria para este producto
            $seccionesStock = [];
            $totalStockSecciones = 0;

            // Existencias reales en lote_secciones para este producto
            $loteSeccionesStock = DB::table('lote_secciones')
                ->join('lotes', 'lotes.id', '=', 'lote_secciones.lote_id')
                ->where('lotes.producto_id', $prod->id)
                ->whereNull('lotes.deleted_at')
                ->when($sucursalId, fn($q) => $q->where('lotes.sucursal_id', $sucursalId))
                ->groupBy('lote_secciones.seccion_id')
                ->select('lote_secciones.seccion_id', DB::raw('SUM(lote_secciones.cantidad_actual) as total_cantidad'))
                ->pluck('total_cantidad', 'seccion_id')
                ->map(fn($val) => (int) $val)
                ->toArray();

            // Stock de lotes huérfanos sin fila en lote_secciones (fallback legacy)
            $stockSinSeccion = (int) DB::table('lotes')
                ->where('producto_id', $prod->id)
                ->whereNull('deleted_at')
                ->where('cantidad_actual', '>', 0)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('lote_secciones')
                        ->whereColumn('lote_secciones.lote_id', 'lotes.id');
                })
                ->sum('cantidad_actual');

            if ($seccionesList->isNotEmpty()) {
                foreach ($seccionesList as $sec) {
                    $cant = $loteSeccionesStock[$sec->id] ?? 0;
                    if ($sec->es_almacen_principal && $stockSinSeccion > 0) {
                        $cant += $stockSinSeccion;
                        $stockSinSeccion = 0;
                    }
                    $seccionesStock[] = [
                        'seccion_id' => $sec->id,
                        'seccion_nombre' => $sec->nombre,
                        'es_principal' => (bool) $sec->es_almacen_principal,
                        'cantidad' => $cant,
                    ];
                    $totalStockSecciones += $cant;

                    if (! isset($granTotalPorSeccion[$sec->nombre])) {
                        $granTotalPorSeccion[$sec->nombre] = 0;
                    }
                    $granTotalPorSeccion[$sec->nombre] += $cant;
                }

                if ($stockSinSeccion > 0 && ! empty($seccionesStock)) {
                    $seccionesStock[0]['cantidad'] += $stockSinSeccion;
                    $totalStockSecciones += $stockSinSeccion;
                    $granTotalPorSeccion[$seccionesStock[0]['seccion_nombre']] += $stockSinSeccion;
                }
            } else {
                $totalLotes = (int) Lote::query()
                    ->where('producto_id', $prod->id)
                    ->whereNull('deleted_at')
                    ->where('cantidad_actual', '>', 0)
                    ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                    ->sum('cantidad_actual');

                $seccionesStock[] = [
                    'seccion_id' => null,
                    'seccion_nombre' => 'Farmacia Central',
                    'es_principal' => true,
                    'cantidad' => $totalLotes,
                ];
                $totalStockSecciones = $totalLotes;

                if (! isset($granTotalPorSeccion['Farmacia Central'])) {
                    $granTotalPorSeccion['Farmacia Central'] = 0;
                }
                $granTotalPorSeccion['Farmacia Central'] += $totalLotes;
            }

            $items[] = [
                'producto_id' => $prod->id,
                'producto_nombre' => $prod->nombre,
                'marca_nombre' => $prod->marca?->nombre ?? 'Sin Marca',
                'unidad_medida' => $prod->unidad_medida ?? 'Unidad',
                'stock_minimo' => (int) $prod->stock_minimo,
                'saldo_inicial' => $saldoInicial,
                'movimientos' => $movimientosFormateados,
                'total_entradas' => $totalEntradasPeriodo,
                'total_salidas' => $totalSalidasPeriodo,
                'saldo_final' => $saldoFinal,
                'secciones_stock' => $seccionesStock,
                'total_stock_secciones' => $totalStockSecciones,
                'ultimo_precio_compra' => $ultimoPrecioCompra,
                'ultimo_precio_venta' => $ultimoPrecioVenta,
                'valor_total_compra' => round($valorCompra, 2),
                'valor_total_venta' => round($valorVenta, 2),
            ];

            $granTotalSaldoInicial += $saldoInicial;
            $granTotalEntradas += $totalEntradasPeriodo;
            $granTotalSalidas += $totalSalidasPeriodo;
            $granTotalSaldoFinal += $saldoFinal;
            $granTotalValorCompra += $valorCompra;
            $granTotalValorVenta += $valorVenta;
        }

        return [
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => $fin->format('Y-m-d'),
            'sucursal_id' => $sucursalId,
            'producto_id' => $productoId,
            'items' => $items,
            'resumen_general' => [
                'total_productos' => count($items),
                'gran_total_saldo_inicial' => $granTotalSaldoInicial,
                'gran_total_entradas' => $granTotalEntradas,
                'gran_total_salidas' => $granTotalSalidas,
                'gran_total_saldo_final' => $granTotalSaldoFinal,
                'gran_total_valor_compra' => round($granTotalValorCompra, 2),
                'gran_total_valor_venta' => round($granTotalValorVenta, 2),
                'margen_bruto_potencial' => round($granTotalValorVenta - $granTotalValorCompra, 2),
                'stock_por_seccion' => $granTotalPorSeccion,
            ],
        ];
    }
}
