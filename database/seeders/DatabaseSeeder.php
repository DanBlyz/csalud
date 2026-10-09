<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SucursalSeeder::class,
            RolSeeder::class,
            EspecialidadSeeder::class,
            PermisoSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            TipoSolicitudSeeder::class,
            MarcaSeeder::class,
            ProveedorSeeder::class,
            ServicioSeeder::class,
            ProductoSeeder::class,
            SeccionSeeder::class,
        ]);
    }
}
