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
        Schema::create('bajas_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('codigo')->unique();
            $table->date('fecha_registro');
            $table->string('area_solicitante');
            $table->string('responsable');
            $table->string('motivo')->default('Vencimiento');
            $table->text('justificacion');
            $table->string('estado')->default('Pendiente'); // Pendiente, Aprobado, Rechazado
            $table->foreignId('aprobado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->text('observaciones_aprobacion')->nullable();

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('baja_medicamento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baja_medicamento_id')->constrained('bajas_medicamentos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete(); // Stock/Inventario de origen
            $table->integer('cantidad');
            $table->string('forma_farmaceutica')->nullable();
            $table->string('concentracion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('baja_medicamento_id')->nullable()->after('proforma_id')->constrained('bajas_medicamentos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['baja_medicamento_id']);
            $table->dropColumn('baja_medicamento_id');
        });

        Schema::dropIfExists('baja_medicamento_detalles');
        Schema::dropIfExists('bajas_medicamentos');
    }
};
