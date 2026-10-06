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
        Schema::create('lote_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->integer('cantidad_actual')->default(0);
            $table->timestamps();

            $table->unique(['lote_id', 'seccion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_secciones');
    }
};
