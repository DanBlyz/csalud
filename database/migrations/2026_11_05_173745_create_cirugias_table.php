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
        Schema::create('cirugias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->string('numero', 50)->nullable(); // N° de descargo de quirófano

            // Régimen / Cobertura
            $table->boolean('es_institucional')->default(true);
            $table->boolean('es_convenio')->default(false);
            $table->string('convenio_nombre', 150)->nullable();

            // Fechas y Ubicación
            $table->date('fecha')->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->string('sala', 50)->nullable();
            $table->string('cama', 50)->nullable();

            // Horarios de Quirófano, Anestesia y Cirugía
            $table->string('hora_ingreso_quirofano', 10)->nullable();
            $table->string('hora_salida_quirofano', 10)->nullable();
            $table->string('hora_inicio_anestesia', 10)->nullable();
            $table->string('hora_fin_anestesia', 10)->nullable();
            $table->string('hora_inicio_cirugia', 10)->nullable();
            $table->string('hora_fin_cirugia', 10)->nullable();

            // Procedimiento Clínico
            $table->text('diagnostico_operacion')->nullable();
            $table->text('cirugia_efectuada')->nullable();
            $table->string('tipo_cirugia', 50)->nullable(); // Ambulatoria, Menor, Media, Mayor, Especial
            $table->string('tipo_anestesia', 100)->nullable();
            $table->string('analgesia', 150)->nullable();

            // Personal que interviene (Encargados vinculados a users)
            $table->foreignId('cirujano_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('primer_ayudante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('segundo_ayudante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anestesiologo_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('instrumentador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('circulante_id')->nullable()->constrained('users')->nullOnDelete();

            // Equipos especiales y solicitudes (Hoja de Enfermería)
            $table->boolean('torre_laparoscopia')->default(false);
            $table->boolean('bomba_infusion')->default(false);
            $table->boolean('maquina_aspiracion')->default(false);
            $table->string('material_importadora', 150)->nullable();
            $table->boolean('intensificador_imagenes')->default(false);
            $table->boolean('maquina_anestesiologia')->default(false);
            $table->boolean('monitor_multiparametrico')->default(false);
            $table->boolean('ecografo')->default(false);
            $table->boolean('rayos_x')->default(false);
            $table->boolean('oxigeno_central')->default(false);
            $table->boolean('electrobisturi')->default(false);
            $table->string('patologia', 150)->nullable();
            $table->string('otros_equipos', 255)->nullable();

            // Observaciones adicionales
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
        Schema::dropIfExists('cirugias');
    }
};
