<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            ['nombre_completo' => 'María Flores Quispe', 'ci' => '1234567', 'edad' => 34, 'sexo' => 'Femenino', 'email' => 'maria.flores@example.com', 'telefono' => '76543210', 'direccion' => 'El Alto - Zona 16 de Julio'],
            ['nombre_completo' => 'Juan Mamani Condori', 'ci' => '2345678', 'edad' => 45, 'sexo' => 'Masculino', 'email' => 'juan.mamani@example.com', 'telefono' => '76543211', 'direccion' => 'La Paz - Miraflores'],
            ['nombre_completo' => 'Ana Rojas Lima', 'ci' => '3456789', 'edad' => 28, 'sexo' => 'Femenino', 'email' => 'ana.rojas@example.com', 'telefono' => '76543212', 'direccion' => 'El Alto - Ciudad Satélite'],
            ['nombre_completo' => 'Carlos Quispe Choque', 'ci' => '4567890', 'edad' => 52, 'sexo' => 'Masculino', 'email' => 'carlos.quispe@example.com', 'telefono' => '76543213', 'direccion' => 'La Paz - Sopocachi'],
            ['nombre_completo' => 'Lucía Vargas Poma', 'ci' => '5678901', 'edad' => 31, 'sexo' => 'Femenino', 'email' => 'lucia.vargas@example.com', 'telefono' => '76543214', 'direccion' => 'El Alto - Senkata'],
            ['nombre_completo' => 'Roberto Aguilar Silva', 'ci' => '6789012', 'edad' => 39, 'sexo' => 'Masculino', 'email' => 'paciente@dialab.com', 'telefono' => '76543215', 'direccion' => 'La Paz - Centro'],
            ['nombre_completo' => 'Elena Gutiérrez Cruz', 'ci' => '7890123', 'edad' => 26, 'sexo' => 'Femenino', 'email' => 'elena.gutierrez@example.com', 'telefono' => '76543216', 'direccion' => 'El Alto - Villa Adela'],
            ['nombre_completo' => 'Diego Fernández Rojas', 'ci' => '8901234', 'edad' => 41, 'sexo' => 'Masculino', 'email' => 'diego.fernandez@example.com', 'telefono' => '76543217', 'direccion' => 'La Paz - Calacoto'],
        ];

        foreach ($pacientes as $data) {
            $paciente = Paciente::firstOrCreate(
                ['ci' => $data['ci']],
                array_merge($data, ['fecha_registro' => Carbon::now()->subDays(rand(1,30))->toDateString(), 'activo' => true])
            );

            // Crear usuario paciente para los que tienen email (portal paciente)
            if (in_array($data['email'], ['paciente@dialab.com', 'maria.flores@example.com'])) {
                User::firstOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['nombre_completo'], 'password' => Hash::make('password123'), 'rol' => 'Paciente', 'activo' => true]
                );
            }
        }
    }
}
