<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('despachos_farmacia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->foreignId('receta_id')->nullable()->constrained('recetas')->nullOnDelete();
            $table->foreignId('receta_detalle_id')->nullable()->constrained('receta_detalles')->nullOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->foreignId('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Migrar y congelar precios históricos de los despachos previos ('Salida Receta' / 'Salida Farmacia')
        $salidas = DB::table('movimientos_inventario')
            ->whereIn('tipo_movimiento', ['Salida Receta', 'Salida Farmacia'])
            ->whereNotNull('proforma_id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        $proformasAfectadas = [];

        foreach ($salidas as $mov) {
            $devueltas = (int) DB::table('movimientos_inventario')
                ->where('movimiento_origen_id', $mov->id)
                ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion', 'Devolución Farmacia', 'Devolucion Farmacia'])
                ->whereNull('deleted_at')
                ->sum('cantidad');

            $cantidadNeta = max(0, (int) $mov->cantidad - $devueltas);

            $recetaDetalle = null;
            if ($mov->receta_id) {
                $recetaDetalle = DB::table('receta_detalles')
                    ->where('receta_id', $mov->receta_id)
                    ->where('producto_id', $mov->producto_id)
                    ->first();
            }

            $lote = $mov->lote_id
                ? DB::table('lotes')->where('id', $mov->lote_id)->first()
                : null;

            $producto = DB::table('productos')->where('id', $mov->producto_id)->first();

            // Priorizar el costo unitario del detalle de receta o el precio de venta del lote despachado en esa fecha
            $precioHistorico = 0.00;
            if ($recetaDetalle && (float) ($recetaDetalle->costo_unitario ?? 0) > 0) {
                $precioHistorico = (float) $recetaDetalle->costo_unitario;
            } elseif ($lote && (float) ($lote->precio_venta ?? 0) > 0) {
                $precioHistorico = (float) $lote->precio_venta;
            } elseif ($producto) {
                $precioHistorico = (float) ($producto->ultimo_precio_venta ?? 0);
            }

            DB::table('despachos_farmacia')->insert([
                'proforma_id' => $mov->proforma_id,
                'receta_id' => $mov->receta_id,
                'receta_detalle_id' => $recetaDetalle?->id,
                'producto_id' => $mov->producto_id,
                'lote_id' => $mov->lote_id,
                'seccion_id' => $mov->seccion_origen_id,
                'movimiento_inventario_id' => $mov->id,
                'cantidad' => $cantidadNeta,
                'precio_unitario' => $precioHistorico,
                'user_id' => $mov->user_id,
                'observaciones' => $mov->observaciones,
                'usuario_creador_id' => $mov->usuario_creador_id ?? $mov->user_id,
                'created_at' => $mov->created_at ?? now(),
                'updated_at' => $mov->updated_at ?? now(),
                'deleted_at' => $cantidadNeta <= 0 ? ($mov->updated_at ?? now()) : null,
            ]);

            $proformasAfectadas[$mov->proforma_id] = $mov->proforma_id;
        }

        // Recalcular el costo_total de las proformas con los precios históricos congelados
        foreach ($proformasAfectadas as $proformaId) {
            $totalServicios = (float) DB::table('proforma_servicios')
                ->where('proforma_id', $proformaId)
                ->whereNull('deleted_at')
                ->sum('costo_final');

            $totalConsumos = (float) DB::table('consumos_extras')
                ->where('proforma_id', $proformaId)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')
                ->value('total');

            $totalFarmacia = (float) DB::table('despachos_farmacia')
                ->where('proforma_id', $proformaId)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')
                ->value('total');

            DB::table('proformas')
                ->where('id', $proformaId)
                ->update([
                    'costo_total' => round($totalServicios + $totalConsumos + $totalFarmacia, 2),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos_farmacia');
    }
};
