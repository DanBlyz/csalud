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
        Schema::create('cierre_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cierre_mensual_id')->constrained('cierres_mensuales')->cascadeOnDelete();
            $table->string('tipo', 10); // 'Ingreso' o 'Egreso'
            $table->string('categoria', 50); // 'Cobro Proforma', 'Honorario Médico', 'Compra Farmacia', 'Servicio Básico', 'Sueldo', 'Gasto Operativo', 'Otro'
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('comprobante_referencia')->nullable();
            $table->string('origen_tipo')->nullable();
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('cierre_detalles');
    }
};
