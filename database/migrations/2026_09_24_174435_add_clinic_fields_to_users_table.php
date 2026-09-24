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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombres')->nullable()->after('name');
            $table->string('apellido_paterno')->nullable()->after('nombres');
            $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            $table->string('cedula')->unique()->nullable()->after('apellido_materno');
            $table->string('celular')->nullable()->after('email');
            $table->string('direccion')->nullable()->after('celular');
            $table->boolean('activo')->default(true)->after('direccion');

            // Llaves foráneas
            $table->foreignId('sucursal_id')->nullable()->after('activo')->constrained('sucursales')->nullOnDelete();
            $table->foreignId('rol_id')->nullable()->after('sucursal_id')->constrained('roles')->nullOnDelete();
            $table->foreignId('especialidad_id')->nullable()->after('rol_id')->constrained('especialidades')->nullOnDelete();

            // Auditoría y SoftDeletes
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['rol_id']);
            $table->dropForeign(['especialidad_id']);

            $table->dropColumn([
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'cedula',
                'celular',
                'direccion',
                'activo',
                'sucursal_id',
                'rol_id',
                'especialidad_id',
                'usuario_creador_id',
                'usuario_modificador_id',
                'usuario_eliminador_id',
                'deleted_at',
            ]);
        });
    }
};
