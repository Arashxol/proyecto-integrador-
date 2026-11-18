<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoAnalisis;

class AnalisisSeeder extends Seeder {
    public function run(): void {
        TipoAnalisis::create(['nombre' => 'Hemograma Completo', 'valores_referencia' => 'Leucocitos: 4.5 - 11.0 x10^3/uL', 'precio' => 45.00, 'categoria' => 'Hematología']);
        TipoAnalisis::create(['nombre' => 'Glucosa en Sangre', 'valores_referencia' => '70 - 100 mg/dL', 'precio' => 25.00, 'categoria' => 'Bioquímica']);
        TipoAnalisis::create(['nombre' => 'Colesterol Total', 'valores_referencia' => 'Menor a 200 mg/dL', 'precio' => 30.00, 'categoria' => 'Bioquímica']);
        TipoAnalisis::create(['nombre' => 'Triglicéridos', 'valores_referencia' => 'Menor a 150 mg/dL', 'precio' => 35.00, 'categoria' => 'Bioquímica']);
        TipoAnalisis::create(['nombre' => 'Creatinina', 'valores_referencia' => '0.7 - 1.3 mg/dL', 'precio' => 28.00, 'categoria' => 'Bioquímica']);
        TipoAnalisis::create(['nombre' => 'Ácido Úrico', 'valores_referencia' => '3.4 - 7.0 mg/dL', 'precio' => 25.00, 'categoria' => 'Bioquímica']);
        TipoAnalisis::create(['nombre' => 'Examen General de Orina', 'valores_referencia' => 'Aspecto: Limpio, pH: 5-8', 'precio' => 30.00, 'categoria' => 'Uroanálisis']);
        TipoAnalisis::create(['nombre' => 'Coproparasitológico Seriado', 'valores_referencia' => 'Negativo (Sin parásitos)', 'precio' => 35.00, 'categoria' => 'Parasitología']);
    }
}
