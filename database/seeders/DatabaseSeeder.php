<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario Administrador para el Centro de Salud
        User::firstOrCreate(
            ['email' => 'admin@csalud.com'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        // Usuario Médico de prueba
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Dr. Usuario Médico',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
