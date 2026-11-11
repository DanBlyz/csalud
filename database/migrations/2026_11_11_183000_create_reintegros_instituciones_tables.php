<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reintegros_instituciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('seccion_destino_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->string('codigo')->unique();
            $table->date('fecha_reintegro');
            $table->string('persona_entrega');
            $table->string('ci_persona_entrega')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('total_unidades')->default(0);
            $table->decimal('monto_total_descontado', 12, 2)->default(0);

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reintegro_institucion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reintegro_institucion_id');
            $table->foreign('reintegro_institucion_id', 'fk_reint_det_cabecera')
                ->references('id')->on('reintegros_instituciones')->cascadeOnDelete();

            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->string('codigo_lote');
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('cantidad_usada_institucion')->default(0);
            $table->integer('cantidad_reintegrada');
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->text('observaciones')->nullable();

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reintegro_institucion_aplicaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reintegro_institucion_id');
            $table->foreign('reintegro_institucion_id', 'fk_reint_apl_cabecera')
                ->references('id')->on('reintegros_instituciones')->cascadeOnDelete();

            $table->unsignedBigInteger('reintegro_institucion_detalle_id');
            $table->foreign('reintegro_institucion_detalle_id', 'fk_reint_apl_detalle')
                ->references('id')->on('reintegro_institucion_detalles')->cascadeOnDelete();

            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            $table->unsignedBigInteger('movimiento_origen_id')->nullable();
            $table->foreign('movimiento_origen_id', 'fk_reint_apl_mov_orig')
                ->references('id')->on('movimientos_inventario')->nullOnDelete();

            $table->unsignedBigInteger('movimiento_devolucion_id')->nullable();
            $table->foreign('movimiento_devolucion_id', 'fk_reint_apl_mov_dev')
                ->references('id')->on('movimientos_inventario')->nullOnDelete();

            $table->string('tipo_origen')->default('Salida Receta');
            $table->integer('cantidad_descontada');
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('subtotal_descontado', 12, 2)->default(0);

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reintegro_institucion_aplicaciones');
        Schema::dropIfExists('reintegro_institucion_detalles');
        Schema::dropIfExists('reintegros_instituciones');
    }
};
