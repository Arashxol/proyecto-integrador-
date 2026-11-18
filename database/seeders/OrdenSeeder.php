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

class OrdenSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::all();
        $tipos = TipoAnalisis::all();
        if ($pacientes->isEmpty() || $tipos->isEmpty()) return;

        $estados = ['Pendiente', 'En Proceso', 'Completada'];
        // Limpiar órdenes demo previas (mantener las 2 originales si existen)
        // Crear 12 órdenes variadas
        for ($i = 0; $i < 12; $i++) {
            $paciente = $pacientes->random();
            $numExamenes = rand(1, 3);
            $examenes = $tipos->random($numExamenes);
            $total = $examenes->sum('precio');
            $estadoOrden = $estados[array_rand($estados)];
            $fechaSolicitud = Carbon::now()->subDays(rand(0, 10));

            $orden = OrdenAnalisis::create([
                'codigo_orden' => 'DIA-' . $fechaSolicitud->year . '-' . strtoupper(Str::random(4)) . $i,
                'paciente_id' => $paciente->id,
                'medico_solicitante' => ['Dr. Pérez', 'Dra. López', 'Particular', 'Dr. Tapia'][array_rand(['Dr. Pérez', 'Dra. López', 'Particular', 'Dr. Tapia'])],
                'diagnostico_presuntivo' => ['Control general', 'Diabetes', 'Infección urinaria', 'Anemia'][array_rand(['Control general', 'Diabetes', 'Infección urinaria', 'Anemia'])],
                'fecha_solicitud' => $fechaSolicitud->toDateString(),
                'fecha_entrega_estimada' => $fechaSolicitud->copy()->addDays(1)->toDateString(),
                'total' => $total,
                'estado' => $estadoOrden,
            ]);

            // Pago asociado
            Pago::create([
                'orden_id' => $orden->id,
                'monto' => $total,
                'estado' => in_array($estadoOrden, ['Completada', 'En Proceso']) ? (rand(0,1) ? 'Pagado' : 'Pendiente') : 'Pendiente',
                'metodo' => ['Efectivo', 'QR', 'Transferencia'][array_rand(['Efectivo', 'QR', 'Transferencia'])],
                'fecha_pago' => $estadoOrden == 'Completada' && rand(0,1) ? Carbon::now() : null,
            ]);

            foreach ($examenes as $examen) {
                $estadoMuestra = match($estadoOrden) {
                    'Pendiente' => 'Recolectado',
                    'En Proceso' => ['En Analisis', 'Recolectado'][array_rand(['En Analisis', 'Recolectado'])],
                    'Completada' => 'Resultado Listo',
                };

                $tipoM = 'Sangre';
                if (Str::contains(strtolower($examen->nombre), ['orina'])) $tipoM = 'Orina';
                if (Str::contains(strtolower($examen->nombre), ['copro', 'heces'])) $tipoM = 'Heces';

                $muestra = Muestra::create([
                    'codigo_muestra' => 'MUE-' . strtoupper(Str::random(6)) . $i . $examen->id,
                    'orden_id' => $orden->id,
                    'tipo_analisis_id' => $examen->id,
                    'tipo_muestra' => $tipoM,
                    'estado' => $estadoMuestra,
                    'fecha_recoleccion' => $fechaSolicitud->copy()->addHours(rand(1, 5)),
                    'fecha_analisis' => $estadoMuestra == 'Resultado Listo' ? $fechaSolicitud->copy()->addHours(rand(6, 20)) : null,
                    'tecnico_asignado_id' => $estadoMuestra != 'Recolectado' ? 3 : null, // lab@dialab
                ]);

                $estadoRes = $estadoMuestra == 'Resultado Listo' ? 'Validado' : 'Pendiente';
                Resultado::create([
                    'muestra_id' => $muestra->id,
                    'resultado' => $estadoRes == 'Validado' ? $this->fakeResultado($examen->nombre) : 'Pendiente de análisis',
                    'valores_referencia' => $examen->valores_referencia,
                    'interpretacion' => $estadoRes == 'Validado' ? 'Valores dentro de rango normal' : null,
                    'bioquimico_validador_id' => $estadoRes == 'Validado' ? 3 : null,
                    'fecha_validacion' => $estadoRes == 'Validado' ? Carbon::now()->subDays(rand(0,2)) : null,
                    'estado' => $estadoRes,
                ]);
            }
        }
    }

    private function fakeResultado($nombre) {
        return match(true) {
            str_contains($nombre, 'Hemograma') => 'Leucocitos 6.2 x10^3/uL',
            str_contains($nombre, 'Glucosa') => rand(75, 110) . ' mg/dL',
            str_contains($nombre, 'Colesterol') => rand(150, 210) . ' mg/dL',
            str_contains($nombre, 'Triglicéridos') => rand(90, 160) . ' mg/dL',
            str_contains($nombre, 'Creatinina') => number_format(rand(70, 130)/100, 2) . ' mg/dL',
            str_contains($nombre, 'Ácido Úrico') => number_format(rand(35, 65)/10, 1) . ' mg/dL',
            str_contains($nombre, 'Orina') => 'pH 6.0, Densidad 1.020',
            str_contains($nombre, 'Copro') => 'Negativo',
            default => 'Resultado normal',
        };
    }
}
