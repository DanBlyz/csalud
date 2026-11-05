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
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->unique()->constrained('proformas')->onDelete('cascade');

            // 1. Filiación complementaria
            $table->string('procedencia')->nullable();
            $table->string('residencia')->nullable();
            $table->string('fuente_informacion')->default('Paciente')->nullable();

            // 2. Anamnesis clínica
            $table->text('motivo_consulta')->nullable();
            $table->text('enfermedad_actual')->nullable();

            // 3. Antecedentes personales
            $table->text('antecedentes_patologicos')->nullable();
            $table->text('antecedentes_no_patologicos')->nullable();

            // 4. Antecedentes ginecológicos (si aplica)
            $table->string('menarca', 50)->nullable();
            $table->string('fum', 50)->nullable();
            $table->string('gestas', 20)->nullable();
            $table->string('partos', 20)->nullable();
            $table->string('cesareas', 20)->nullable();
            $table->string('abortos', 20)->nullable();
            $table->string('pap', 100)->nullable();
            $table->string('gineco_otros')->nullable();

            // 5. Examen físico: Signos vitales y descripción
            $table->string('pa', 30)->nullable();
            $table->string('fc', 30)->nullable();
            $table->string('fr', 30)->nullable();
            $table->string('temperatura', 30)->nullable();
            $table->string('sato2', 30)->nullable();
            $table->string('peso', 30)->nullable();
            $table->string('talla', 30)->nullable();
            $table->string('imc', 30)->nullable();
            $table->text('examen_fisico')->nullable();

            // 6. Diagnóstico y Conducta
            $table->text('diagnostico')->nullable();
            $table->text('conducta')->nullable();

            // Auditoría y trazabilidad
            $table->foreignId('usuario_creador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_modificador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
