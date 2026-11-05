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
        Schema::table('recetas', function (Blueprint $table) {
            $table->foreignId('cirugia_id')->nullable()->after('proforma_id')->constrained('cirugias')->cascadeOnDelete();
            $table->string('tipo', 50)->default('Piso')->after('cirugia_id'); // 'Piso', 'Quirófano'
        });

        Schema::table('receta_detalles', function (Blueprint $table) {
            $table->foreignId('seccion_id')->nullable()->after('producto_id')->constrained('secciones')->nullOnDelete();
            $table->string('area_quirurgica', 50)->nullable()->after('seccion_id'); // 'Anestesiología', 'Enfermería', 'General'
            $table->decimal('costo_unitario', 10, 2)->nullable()->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receta_detalles', function (Blueprint $table) {
            $table->dropForeign(['seccion_id']);
            $table->dropColumn(['seccion_id', 'area_quirurgica', 'costo_unitario']);
        });

        Schema::table('recetas', function (Blueprint $table) {
            $table->dropForeign(['cirugia_id']);
            $table->dropColumn(['cirugia_id', 'tipo']);
        });
    }
};
