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
        Schema::create('epicrisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->unique()->constrained('proformas')->onDelete('cascade');

            // 1. Datos operativos de egreso
            $table->string('cama')->nullable();
            $table->string('servicio')->nullable();
            $table->dateTime('fecha_egreso')->nullable();

            // 2. Condiciones y diagnósticos de ingreso y egreso
            $table->text('condiciones_ingreso')->nullable();
            $table->text('diagnostico_ingreso')->nullable();
            $table->text('condiciones_egreso')->nullable();
            $table->text('diagnostico_egreso')->nullable();
            $table->string('causa_egreso')->nullable();

            // 3. Bloques clínicos detallados
            $table->text('estudios_complementarios')->nullable();
            $table->text('antecedentes_relevantes')->nullable();
            $table->text('historia_medica_actual')->nullable();
            $table->text('procedimientos')->nullable();
            $table->text('tratamiento_realizado')->nullable();
            $table->text('indicaciones_alta')->nullable();
            $table->string('proxima_cita')->nullable();
            $table->text('observaciones')->nullable();

            // Auditoría y trazabilidad
            $table->foreignId('usuario_creador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_modificador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_eliminador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epicrisis');
    }
};
