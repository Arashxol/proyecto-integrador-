<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\OrdenAnalisis;
use App\Models\Muestra;
use App\Models\Resultado;
use App\Models\TipoAnalisis;
use App\Models\Pago;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MLSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::all();
        $tipos = TipoAnalisis::all();

        if ($pacientes->isEmpty() || $tipos->isEmpty()) {
            echo "Se necesitan pacientes y tipos de análisis primero.\n";
            return;
        }

        echo "Generando datos para detección de anomalías...\n";

        // Limpiar previos
        Resultado::whereIn('muestra_id', function($q) {
            $q->from('muestras')->whereRaw("codigo_muestra LIKE 'ML-%'");
        })->delete();
        Muestra::where('codigo_muestra', 'LIKE', 'ML-%')->delete();
        OrdenAnalisis::where('codigo_orden', 'LIKE', 'ML-%')->delete();

        $contador = 0;

        // Crear 50 órdenes con resultados variados (algunos normales, algunos anómalos)
        for ($i = 0; $i < 50; $i++) {
            $paciente = $pacientes->random();
            $numExamenes = rand(1, 3);
            $examenes = $tipos->random($numExamenes);
            $total = $examenes->sum('precio');
            $fechaSolicitud = Carbon::now()->subDays(rand(0, 60));

            $orden = OrdenAnalisis::create([
                'codigo_orden' => 'ML-' . $fechaSolicitud->year . '-' . strtoupper(Str::random(4)) . str_pad($i, 3, '0', STR_PAD_LEFT),
                'paciente_id' => $paciente->id,
                'medico_solicitante' => ['Dr. Pérez', 'Dra. López', 'Dr. García', 'Dra. Martínez'][array_rand(['Dr. Pérez', 'Dra. López', 'Dr. García', 'Dra. Martínez'])],
                'diagnostico_presuntivo' => ['Control general', 'Diabetes', 'Control rutinario', 'Chequeo anual'][array_rand(['Control general', 'Diabetes', 'Control rutinario', 'Chequeo anual'])],
                'fecha_solicitud' => $fechaSolicitud->toDateString(),
                'fecha_entrega_estimada' => $fechaSolicitud->copy()->addDays(1)->toDateString(),
                'total' => $total,
                'estado' => 'Completada',
            ]);

            Pago::create([
                'orden_id' => $orden->id,
                'monto' => $total,
                'estado' => 'Pagado',
                'metodo' => ['Efectivo', 'QR', 'Transferencia'][array_rand(['Efectivo', 'QR', 'Transferencia'])],
                'fecha_pago' => Carbon::now(),
            ]);

            foreach ($examenes as $examen) {
                $tipoM = 'Sangre';
                if (Str::contains(strtolower($examen->nombre), ['orina'])) $tipoM = 'Orina';
                if (Str::contains(strtolower($examen->nombre), ['copro', 'heces'])) $tipoM = 'Heces';

                $muestra = Muestra::create([
                    'codigo_muestra' => 'ML-' . strtoupper(Str::random(6)) . $i . $examen->id,
                    'orden_id' => $orden->id,
                    'tipo_analisis_id' => $examen->id,
                    'tipo_muestra' => $tipoM,
                    'estado' => 'Resultado Listo',
                    'fecha_recoleccion' => $fechaSolicitud->copy()->addHours(rand(1, 5)),
                    'fecha_analisis' => $fechaSolicitud->copy()->addHours(rand(6, 20)),
                    'tecnico_asignado_id' => 3,
                ]);

                // 90% normales, 10% anómalos
                $esAnomala = rand(1, 100) <= 10;
                $resultadoTexto = $this->generarResultado($examen->nombre, $examen->valores_referencia, $esAnomala);

                Resultado::create([
                    'muestra_id' => $muestra->id,
                    'resultado' => $resultadoTexto,
                    'valores_referencia' => $examen->valores_referencia,
                    'interpretacion' => $esAnomaliola ? 'Valor fuera de rango - Requiere revisión' : 'Valores dentro de rango normal',
                    'bioquimico_validador_id' => 3,
                    'fecha_validacion' => Carbon::now()->subDays(rand(0, 5)),
                    'estado' => 'Validado',
                ]);
                $contador++;
            }
        }

        echo "Se generaron {$contador} resultados para detección de anomalías.\n";
    }

    private function generarResultado($nombre, $referencia, $esAnomala)
    {
        preg_match_all('/[\d.]+/', $referencia, $nums);

        if (count($nums[0]) >= 2) {
            $min = (float)$nums[0][0];
            $max = (float)$nums[0][1];
            $promedio = ($min + $max) / 2;

            if ($esAnomala) {
                // Valor anómalo: muy alto o muy bajo
                if (rand(0, 1)) {
                    $valor = $max + rand(20, 300); // Muy alto
                } else {
                    $valor = max(1, $min - rand(5, 50)); // Muy bajo
                }
            } else {
                // Valor normal con variación pequeña
                $valor = $promedio + rand(-10, 15);
                if ($valor < $min) $valor = $min;
                if ($valor > $max) $valor = $max;
            }

            // Formato según tipo de examen
            if (stripos($nombre, 'glucosa') !== false) {
                return number_format($valor, 1) . ' mg/dL';
            } elseif (stripos($nombre, 'colesterol') !== false) {
                return number_format($valor, 1) . ' mg/dL';
            } elseif (stripos($nombre, 'creatinina') !== false) {
                return number_format($valor, 2) . ' mg/dL';
            } elseif (stripos($nombre, 'ácido úrico') !== false || stripos($nombre, 'acido urico') !== false) {
                return number_format($valor, 1) . ' mg/dL';
            } elseif (stripos($nombre, 'hemograma') !== false) {
                return number_format($valor, 1) . ' x10^3/uL';
            } elseif (stripos($nombre, 'triglicéridos') !== false || stripos($nombre, 'trigliceridos') !== false) {
                return number_format($valor, 1) . ' mg/dL';
            } else {
                return number_format($valor, 1);
            }
        }

        return 'Resultado normal';
    }
}