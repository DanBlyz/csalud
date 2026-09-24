<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Sucursal Central Inicial
        $sucursalId = DB::table('sucursales')->insertGetId([
            'nombre' => 'Sede Central',
            'direccion' => 'Av. de la Salud #123, Zona Central',
            'telefono' => '4-4123456',
            'ciudad' => 'Central',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Roles Principales
        $roles = [
            'Admin' => 'Superadministrador con acceso total al sistema',
            'Médico' => 'Atención clínica, prescripción de recetas y solicitudes',
            'Enfermería' => 'Triaje, control de piso y consumos extras',
            'Farmacia' => 'Control de lotes y despacho de medicamentos',
            'Caja' => 'Liquidación de proformas y cobros',
            'Recepción' => 'Admisión de pacientes y apertura de proformas',
        ];

        $roleIds = [];
        foreach ($roles as $nombre => $descripcion) {
            $roleIds[$nombre] = DB::table('roles')->insertGetId([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Especialidades Médicas
        $especialidades = ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología', 'Cardiología'];
        $especialidadIds = [];
        foreach ($especialidades as $esp) {
            $especialidadIds[$esp] = DB::table('especialidades')->insertGetId([
                'nombre' => $esp,
                'descripcion' => 'Especialidad médica de '.$esp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Permisos del Sistema (IDs fijos para control de Middleware)
        $permisos = [
            1 => ['nombre' => 'gestion-usuarios', 'descripcion' => 'Administración de usuarios y personal'],
            2 => ['nombre' => 'gestion-sucursales', 'descripcion' => 'Administración de sedes y sucursales'],
            3 => ['nombre' => 'gestion-roles', 'descripcion' => 'Administración de roles y permisos'],
            4 => ['nombre' => 'gestion-catalogos', 'descripcion' => 'Administración de categorías, marcas y proveedores'],
            5 => ['nombre' => 'gestion-pacientes', 'descripcion' => 'Registro y edición de pacientes'],
            6 => ['nombre' => 'crear-proforma', 'descripcion' => 'Apertura y registro previo de proformas'],
            7 => ['nombre' => 'gestionar-proforma', 'descripcion' => 'Gestión detallada de servicios, recetas y calendario'],
            8 => ['nombre' => 'emitir-receta', 'descripcion' => 'Prescripción de medicamentos'],
            9 => ['nombre' => 'despachar-farmacia', 'descripcion' => 'Despacho de medicamentos y control de lotes'],
            10 => ['nombre' => 'cobro-caja', 'descripcion' => 'Cobro y liquidación de pagos en caja'],
        ];

        foreach ($permisos as $id => $permiso) {
            DB::table('permisos')->insertOrIgnore([
                'id' => $id,
                'nombre' => $permiso['nombre'],
                'descripcion' => $permiso['descripcion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Usuario Administrador General
        $admin = User::updateOrCreate(
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
                'rol_id' => $roleIds['Admin'],
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        // 6. Usuario Médico de Prueba
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
                'rol_id' => $roleIds['Médico'],
                'especialidad_id' => $especialidadIds['Medicina General'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar permisos básicos al médico
        DB::table('permiso_usuario')->insertOrIgnore([
            ['user_id' => $medico->id, 'permiso_id' => 5, 'created_at' => now(), 'updated_at' => now()], // gestion-pacientes
            ['user_id' => $medico->id, 'permiso_id' => 7, 'created_at' => now(), 'updated_at' => now()], // gestionar-proforma
            ['user_id' => $medico->id, 'permiso_id' => 8, 'created_at' => now(), 'updated_at' => now()], // emitir-receta
        ]);

        // 7. Catálogos Iniciales
        DB::table('categorias')->insertOrIgnore([
            ['nombre' => 'Consultas Médicas', 'descripcion' => 'Atenciones médicas ambulatorias', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Procedimientos Menores', 'descripcion' => 'Suturas, curaciones y vendajes', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicios de Enfermería', 'descripcion' => 'Inyectables, sueros y tomas de signos', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Laboratorio y Diagnóstico', 'descripcion' => 'Exámenes y análisis clínicos', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('tipos_solicitudes')->insertOrIgnore([
            ['nombre' => 'Hemograma Completo', 'descripcion' => 'Laboratorio sanguíneo integral', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Radiografía de Tórax', 'descripcion' => 'Estudio radiológico de tórax AP', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ecografía Abdominal', 'descripcion' => 'Rastreo ecográfico abdominal completo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Examen General de Orina', 'descripcion' => 'Laboratorio de orina completo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('marcas')->insertOrIgnore([
            ['nombre' => 'Bayer', 'descripcion' => 'Laboratorio farmacéutico internacional', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bagó', 'descripcion' => 'Laboratorios Bagó', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Genfar', 'descripcion' => 'Medicamentos genéricos de calidad', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nipro Medical', 'descripcion' => 'Insumos y material descartable médico', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('proveedores')->insertOrIgnore([
            ['razon_social' => 'Droguería Inti S.A.', 'nit_ruc' => '1020304050', 'contacto_nombre' => 'Lic. Fernando Rojas', 'celular' => '71122334', 'created_at' => now(), 'updated_at' => now()],
            ['razon_social' => 'Distribuidora Médica Boliviana', 'nit_ruc' => '2030405060', 'contacto_nombre' => 'Dra. Patricia Lima', 'celular' => '72233445', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
