<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de Secciones / Áreas de la Clínica o Centro de Salud
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('es_almacen_principal')->default(false); // True para Farmacia Central (recepción de compras)
            $table->boolean('activo')->default(true);

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabla pivote de Existencias Físicas por Lote y Sección
        Schema::create('lote_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->integer('cantidad_actual')->default(0);
            $table->timestamps();

            $table->unique(['lote_id', 'seccion_id']);
        });

        // 3. Trazabilidad de Transferencias Internas en Kardex
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('seccion_origen_id')->nullable()->after('proforma_id')->constrained('secciones')->nullOnDelete();
            $table->foreignId('seccion_destino_id')->nullable()->after('seccion_origen_id')->constrained('secciones')->nullOnDelete();
        });

        // 4. Sembrado inicial y migración transparente de datos existentes
        $sucursales = DB::table('sucursales')->get();

        foreach ($sucursales as $suc) {
            // Secciones predeterminadas de atención médica
            $farmaciaId = DB::table('secciones')->insertGetId([
                'sucursal_id' => $suc->id,
                'nombre' => 'Farmacia Central',
                'descripcion' => 'Almacén central y dispensación general de medicamentos',
                'es_almacen_principal' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('secciones')->insert([
                [
                    'sucursal_id' => $suc->id,
                    'nombre' => 'Emergencias',
                    'descripcion' => 'Botiquín satélite de shock y atención de urgencias',
                    'es_almacen_principal' => false,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sucursal_id' => $suc->id,
                    'nombre' => 'Quirófano',
                    'descripcion' => 'Insumos quirúrgicos, anestésicos y material estéril de quirófano',
                    'es_almacen_principal' => false,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sucursal_id' => $suc->id,
                    'nombre' => 'Enfermería',
                    'descripcion' => 'Estación de enfermería para piso y suministros hospitalarios',
                    'es_almacen_principal' => false,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Mapear todo el stock actual de lotes de esta sucursal a Farmacia Central
            $lotes = DB::table('lotes')->where('sucursal_id', $suc->id)->get();
            foreach ($lotes as $lote) {
                DB::table('lote_secciones')->insert([
                    'lote_id' => $lote->id,
                    'seccion_id' => $farmaciaId,
                    'cantidad_actual' => $lote->cantidad_actual,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['seccion_origen_id']);
            $table->dropForeign(['seccion_destino_id']);
            $table->dropColumn(['seccion_origen_id', 'seccion_destino_id']);
        });

        Schema::dropIfExists('lote_secciones');
        Schema::dropIfExists('secciones');
    }
};
