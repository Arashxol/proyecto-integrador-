<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Admin DIALAB',
            'email' => 'admin@dialab.com',
            'password' => Hash::make('password123'),
            'rol' => 'Administrador',
        ]);

        // Usuario Recepcionista
        User::create([
            'name' => 'Recepción Centro',
            'email' => 'recepcion@dialab.com',
            'password' => Hash::make('password123'),
            'rol' => 'Recepcionista',
        ]);

        // Usuario Bioquímico
        User::create([
            'name' => 'Dr. Bioquímico',
            'email' => 'laboratorio@dialab.com',
            'password' => Hash::make('password123'),
            'rol' => 'Bioquimico',
        ]);

        // Catálogo y datos de prueba
        $this->call([
            AnalisisSeeder::class,
            PacienteSeeder::class,
            OrdenSeeder::class,
            MLSeeder::class,
        ]);
    }
}