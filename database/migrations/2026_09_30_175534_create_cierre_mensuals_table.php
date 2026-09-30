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
        Schema::create('cierres_mensuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes'); // 1 al 12
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('total_ingresos', 12, 2)->default(0.00);
            $table->decimal('total_egresos', 12, 2)->default(0.00);
            $table->decimal('utilidad_neta', 12, 2)->default(0.00);
            $table->string('estado', 20)->default('Borrador'); // Borrador, Cerrado, Anulado
            $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('cierres_mensuales');
    }
};
