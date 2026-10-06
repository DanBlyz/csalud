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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('proforma_id')->nullable()->constrained('proformas')->nullOnDelete();
            $table->string('tipo_movimiento')->default('Ingreso Proforma'); // Ingreso Proforma, Ingreso Extra, Egreso Caja
            $table->string('categoria')->default('Proforma'); // Proforma, Extra, Gasto Operativo, Servicio Básico, Insumos, etc.
            $table->string('tipo_pago')->default('Efectivo'); // Efectivo, QR, Transferencia
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->string('numero_referencia')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

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
        Schema::dropIfExists('pagos');
    }
};
