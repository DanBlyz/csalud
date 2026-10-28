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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('movimiento_origen_id')->nullable()->after('proforma_id')->constrained('movimientos_inventario')->nullOnDelete();
            $table->string('observaciones', 500)->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['movimiento_origen_id']);
            $table->dropColumn(['movimiento_origen_id', 'observaciones']);
        });
    }
};
