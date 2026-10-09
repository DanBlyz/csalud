<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sucursal = Sucursal::where('nombre', 'Sede Central')->first();
        $sucursalId = $sucursal?->id;

        $rolAdmin = Rol::where('nombre', 'Admin')->first();
        $rolMedico = Rol::where('nombre', 'Médico')->first();

        $espMedicinaGeneral = Especialidad::where('nombre', 'Medicina General')->first();

        // 1. Administrador General
        User::updateOrCreate(
            ['email' => 'admin@csalud.com'],
            [
                'name' => 'Administrador General',
                'nombres' => 'Administrador',
                'apellido_paterno' => 'General',
                'apellido_materno' => 'Sistema',
                'cedula' => '1000001',
                'celular' => '70000001',
                'direccion' => 'Sede Central',
                'activo' => true,
                'sucursal_id' => $sucursalId,
                'rol_id' => $rolAdmin?->id,
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Médico de Prueba
        $medico = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Dr. Usuario Médico',
                'nombres' => 'Carlos',
                'apellido_paterno' => 'Morales',
                'apellido_materno' => 'Paredes',
                'cedula' => '2000002',
                'celular' => '70000002',
                'direccion' => 'Consultorio 1, Sede Central',
                'activo' => true,
                'sucursal_id' => $sucursalId,
                'rol_id' => $rolMedico?->id,
                'especialidad_id' => $espMedicinaGeneral?->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar permisos básicos al médico (5: gestion-pacientes, 7: gestionar-proforma, 8: emitir-receta)
        $medico->permisos()->syncWithoutDetaching([5, 7, 8]);
    }
}
