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
        Schema::create('recetas_externas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('medico_nombre')->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->text('diagnostico')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('receta_externa_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_externa_id')->constrained('recetas_externas')->cascadeOnDelete();
            $table->string('medicamento');
            $table->string('cantidad');
            $table->text('indicaciones')->nullable();

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
        Schema::dropIfExists('receta_externa_detalles');
        Schema::dropIfExists('recetas_externas');
    }
};

