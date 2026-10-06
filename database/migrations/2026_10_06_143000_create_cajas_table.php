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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('monto_apertura', 10, 2)->default(0.00);
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->string('estado')->default('Abierta'); // Abierta, Cerrada

            // Montos declarados en el arqueo al cierre (conteo del cajero)
            $table->decimal('monto_cierre_efectivo', 10, 2)->nullable();
            $table->decimal('monto_cierre_qr', 10, 2)->nullable();
            $table->decimal('monto_cierre_transferencia', 10, 2)->nullable();

            // Totales consolidados por el sistema al cierre
            $table->decimal('total_ingresos_efectivo', 10, 2)->default(0.00);
            $table->decimal('total_ingresos_qr', 10, 2)->default(0.00);
            $table->decimal('total_ingresos_transferencia', 10, 2)->default(0.00);
            $table->decimal('total_egresos_efectivo', 10, 2)->default(0.00);
            $table->decimal('saldo_esperado_efectivo', 10, 2)->default(0.00);
            $table->decimal('diferencia_efectivo', 10, 2)->default(0.00);

            $table->text('observaciones_apertura')->nullable();
            $table->text('observaciones_cierre')->nullable();

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
        Schema::dropIfExists('cajas');
    }
};
