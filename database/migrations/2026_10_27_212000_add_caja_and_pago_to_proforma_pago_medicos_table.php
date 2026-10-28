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
        Schema::table('proforma_pago_medicos', function (Blueprint $table) {
            $table->foreignId('caja_id')->nullable()->after('proforma_id')->constrained('cajas')->nullOnDelete();
            $table->foreignId('pago_id')->nullable()->after('caja_id')->constrained('pagos')->nullOnDelete();
            $table->string('tipo_pago', 50)->default('Efectivo')->after('monto');
            $table->string('numero_referencia', 100)->nullable()->after('tipo_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_pago_medicos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pago_id');
            $table->dropConstrainedForeignId('caja_id');
            $table->dropColumn(['tipo_pago', 'numero_referencia']);
        });
    }
};
