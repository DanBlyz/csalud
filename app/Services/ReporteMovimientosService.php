<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Carbon\Carbon;

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
     *             cantidad_entrada: int,
     *             cantidad_salida: int,
     *             saldo_acumulado: int,
     *             referencia: string,
     *             usuario: string
     *         }>,
     *         total_entradas: int,
     *         total_salidas: int,
     *         saldo_final: int,
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
     *         margen_bruto_potencial: float
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

        foreach ($productos as $prod) {
            // 2. Saldo Inicial: Movimientos previos a la fecha_inicio
            $movsPrevios = MovimientoInventario::query()
                ->where('producto_id', $prod->id)
                ->where('created_at', '<', $inicio)
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
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
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
                ->with(['lote', 'user', 'proforma.paciente', 'receta'])
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

                $movimientosFormateados[] = [
                    'id' => $m->id,
                    'fecha' => $m->created_at->format('d/m/Y H:i'),
                    'tipo_movimiento' => $m->tipo_movimiento,
                    'es_entrada' => $esEntrada,
                    'lote_codigo' => $m->lote?->codigo_lote ?? 'Sin Lote',
                    'lote_vencimiento' => $m->lote?->fecha_vencimiento ? $m->lote->fecha_vencimiento->format('d/m/Y') : null,
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
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
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
            ],
        ];
    }
}
