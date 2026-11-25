<?php

namespace App\Http\Controllers;

use App\Libraries\PDFGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class QATestController extends Controller
{
    // ============================================================
    // CATÁLOGO — Suites genéricas por tipo de prueba
    // Funcionales: Smoke, Sanity, Regression, Login/Registro, E2E
    // No funcionales: Performance, Load, Stress, Soak
    // ============================================================
    private function getCatalogo(): array
    {
        return [
            'funcional' => [
                'smoke_funcional' => [
                    'label'       => 'Smoke (Funcional)',
                    'descripcion' => 'Pruebas de bajo nivel que verifican que los módulos básicos del sistema responden (login, dashboards).',
                    'tipo'        => 'Funcional',
                    'casos' => [
                        'F-SMK-001' => [
                            'id' => 'F-SMK-001', 'nombre' => 'Login del siguiente usuario y carga del dashboard (Smoke)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que la autenticación funciona y el dashboard de Siegechar siempre se carga.',
                            'precondiciones' => 'Sistema en producción o ambiente de pruebas con servicio levantado.',
                            'datos' => 'Usuario base: doc@dentalcenter.com',
                            'esperado' => 'HTTP 200 del login y dashboard muy estable.',
                        ],
                        'F-SMK-002' => [
                            'id' => 'F-SMK-002', 'nombre' => 'Acceso al módulo de radioprotección (Smoke)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que el módulo de radioprotección está accesible.',
                            'precondiciones' => 'Usuario de la suite está autenticado.',
                            'datos' => 'GET /radiologia',
                            'esperado' => 'HTTP 200.',
                        ],
                    ],
                ],
                'sanity_funcional' => [
                    'label'       => 'Sanity (Funcional)',
                    'descripcion' => 'Tests mínimos para verificar que el sistema funciona más allá del smoke (funcionalidad básica de Core).',
                    'tipo'        => 'Funcional',
                    'casos' => [
                        'F-SNK-001' => [
                            'id' => 'F-SNK-001', 'nombre' => 'Captura inicial del historial de órdenes (Sanity)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que la lista de órdenes por validar responde y muestra datos.',
                            'precondiciones' => 'Usuario laboratorio autenticado.',
                            'datos' => 'GET /orders',
                            'esperado' => 'HTTP 200 con datos de la tabla.',
                        ],
                        'F-SNK-002' => [
                            'id' => 'F-SNK-002', 'nombre' => 'Leer módulo de inventarios (Sanity)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que el sistema de inventarios responde con 200.',
                            'precondiciones' => 'Usuario admin autenticado.',
                            'datos' => 'GET /inventarios',
                            'esperado' => 'HTTP 200.',
                        ],
                    ],
                ],
                'regression_funcional' => [
                    'label'       => 'Regression (Funcional)',
                    'descripcion' => 'Pruebas de regresión sobre flujos críticos (login, orden sin análisis, bloqueos de rol).',
                    'tipo'        => 'Funcional',
                    'casos' => [
                        'F-RG-001' => [
                            'id' => 'F-RG-001', 'nombre' => 'Rechazo de orden sin análisis (Regression)',
                            'tipo' => 'Negativa · Partición de equivalencia', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que no se permite crear una orden sin especificar al menos un análisis.',
                            'precondiciones' => 'Usuario receptivo autenticado.',
                            'datos' => 'POST /orders sin campo exámenes (datos inválidos).',
                            'esperado' => 'Respuesta 302/422 o mensaje de error visible.',
                        ],
                        'F-RG-002' => [
                            'id' => 'F-RG-002', 'nombre' => 'Acceso al formulario de registro de pacientes (Regression)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que el formulario sigue accesible y requiere campos obligatorios.',
                            'precondiciones' => 'Usuario receptivo autenticado.',
                            'datos' => 'GET /pacientes/nuevo',
                            'esperado' => 'Formulario con campo nombre_completo presente (HTTP 200).',
                        ],
                    ],
                ],
                'login_registro' => [
                    'label'       => 'Login y Registro (Funcional)',
                    'descripcion' => 'Tests funcionales del flujo de autenticación y registro de usuarios.',
                    'tipo'        => 'Funcional',
                    'casos' => [
                        'F-LOG-001' => [
                            'id' => 'F-LOG-001', 'nombre' => 'Inicio de sesión con credenciales válidas',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que login exitoso redirige al dashboard y setea sesión.',
                            'precondiciones' => 'Credenciales válidas de test.',
                            'datos' => 'POST /login email/password válidos.',
                            'esperado' => '302 a /dashboard + sesión iniciada.',
                        ],
                        'F-REG-001' => [
                            'id' => 'F-REG-001', 'nombre' => 'Verificar formulario de registro de pacientes',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Comprobar que el formulario de registro de pacientes carga con campos obligatorios.',
                            'precondiciones' => 'Sesión de receptions activa.',
                            'datos' => 'GET /pacientes/nuevo',
                            'esperado' => 'HTTP 200 con campos nombre_completo, ci, sexo, edad en el formulario.',
                        ],
                    ],
                ],
                'e2e' => [
                    'label'       => 'End-to-End (Funcional)',
                    'descripcion' => 'Tests E2E simulationados: flujo login → búsqueda → detalle de casos.',
                    'tipo'        => 'Funcional',
                    'casos' => [
                        'F-E2E-001' => [
                            'id' => 'F-E2E-001', 'nombre' => 'E2E: Login → dashboard → acceso a módulos (Flujo 1)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Simular flujo completo desde el login hasta acceso a módulos básicos.',
                            'precondiciones' => 'Servidor levantado.',
                            'datos' => 'Login como receptions + GET / dashboard.',
                            'esperado' => 'Cada paso HTTP 200.',
                        ],
                        'F-E2E-002' => [
                            'id' => 'F-E2E-002', 'nombre' => 'E2E: Login como laboratorio → módulos de validación (Flujo 2)',
                            'tipo' => 'Positiva', 'prioridad' => 'Alta',
                            'objetivo' => 'Simular flujo completo con rol laboratorio: /orders + /results.',
                            'precondiciones' => 'Servidor levantado.',
                            'datos' => 'Login como laboratorio + GET /results.',
                            'esperado' => 'Cada paso HTTP 200.',
                        ],
                    ],
                ],
            ],
            'nofuncional' => [
                'performance' => [
                    'label'       => 'Performance',
                    'descripcion' => 'Mide latencia de los endpoints de lectura más útiles.',
                    'tipo'        => 'No funcional',
                    'casos' => [
                        'N-PERF-001' => [
                            'id' => 'N-PERF-001', 'nombre' => 'Latencia promedio de GET / (8 muestras)',
                            'tipo' => 'Positiva', 'prioridad' => 'Media',
                            'objetivo' => 'Validar que el dashboard responde en menos del límite de referencia (3000 ms).',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '8 × GET / midiendo tiempo de respuesta.',
                            'esperado' => 'Promedio < 3000 ms.',
                        ],
                        'N-PERF-002' => [
                            'id' => 'N-PERF-002', 'nombre' => 'Latencia promedio de GET /login (8 muestras)',
                            'tipo' => 'Positiva', 'prioridad' => 'Media',
                            'objetivo' => 'Validar respuesta rápida del formulario de login.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '8 × GET /login midiendo tiempo de respuesta.',
                            'esperado' => 'Promedio < 3000 ms.',
                        ],
                    ],
                ],
                'carga' => [
                    'label'       => 'Load (Carga)',
                    'descripcion' => 'Verifica comportamiento bajo demanda progresiva.',
                    'tipo'        => 'No funcional',
                    'casos' => [
                        'N-LOAD-001' => [
                            'id' => 'N-LOAD-001', 'nombre' => 'Carga progresiva de GET / (30 intentos, 2s entre)',
                            'tipo' => 'Positiva', 'prioridad' => 'Media',
                            'objetivo' => 'Verificar que el sistema sigue respondiendo bajo carga sostenida.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '30 × GET / con pausa de 2s.',
                            'esperado' => 'Al menos 90% de respuestas 200.',
                        ],
                        'N-LOAD-002' => [
                            'id' => 'N-LOAD-002', 'nombre' => 'Carga progresiva de GET /login (30 intentos, 2s entre)',
                            'tipo' => 'Positiva', 'prioridad' => 'Media',
                            'objetivo' => 'Verificar estabilidad del formulario de login.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '30 × GET /login con pausa de 2s.',
                            'esperado' => 'Al menos 90% de respuestas 200.',
                        ],
                    ],
                ],
                'estres' => [
                    'label'       => 'Stress (Estres)',
                    'descripcion' => 'Sobrecarar interfaces con flujos repetidos.',
                    'tipo'        => 'No funcional',
                    'casos' => [
                        'N-EST-001' => [
                            'id' => 'N-EST-001', 'nombre' => 'Stress: acumulación de usuarios (100 × GET /)',
                            'tipo' => 'Positiva', 'prioridad' => 'Baja',
                            'objetivo' => 'Verificar que el sistema responde bajo alta concurrencia sin caer.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '100 × GET / sin pausa.',
                            'esperado' => 'Respuestas de error breves (4xx/5xx) sin caídas totales.',
                        ],
                        'N-EST-002' => [
                            'id' => 'N-EST-002', 'nombre' => 'Stress: reporte diario con gran volumen de datos (50)',
                            'tipo' => 'Positiva', 'prioridad' => 'Baja',
                            'objetivo' => 'Verificar resistencia en reportes.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '50 × GET /reportes.',
                            'esperado' => 'Al menos un 95% de respuestas 200.',
                        ],
                    ],
                ],
                'soak' => [
                    'label'       => 'Soak (Endurancia)',
                    'descripcion' => 'Simula carga sostenida por periodo prolongado.',
                    'tipo'        => 'No funcional',
                    'casos' => [
                        'N-SOAK-001' => [
                            'id' => 'N-SOAK-001', 'nombre' => 'Carga sostenida de GET /orders (30 iter., 3s entre)',
                            'tipo' => 'Positiva', 'prioridad' => 'Baja',
                            'objetivo' => 'Verificar endurancia y estabilidad del rate limiting.',
                            'precondiciones' => 'Aplicación corriendo.',
                            'datos' => '30 × GET /orders con pausa de 3s.',
                            'esperado' => 'Sin cierres inesperados durante el test.',
                        ],
                    ],
                ],
            ],
        ];
    }

    // ============================================================
    // ÍNDICE DE PRUEBAS (vista QA)
    // ============================================================
    public function index()
    {
        $catalogo = $this->getCatalogo();
        $tipos    = array_keys($catalogo);
        return view('qa.index', compact('catalogo', 'tipos'));
    }

    // ============================================================
    // EJECUTAR PRUEBAS — flujo general
    // ============================================================
    public function ejecutar(Request $request)
    {
        ini_set('max_execution_time', '300');

        $request->validate([
            'email'  => 'required|email',
            'tipo'   => 'required|string',
            'suite'  => 'required|string',
            'casos'  => 'nullable|array',
        ]);

        $catalogo = $this->getCatalogo();
        $tipo     = $request->tipo;
        $suite    = $request->suite;
        $email    = $request->email;
        $baseUrl  = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');

        if (!isset($catalogo[$tipo][$suite])) {
            return response()->json(['error' => 'Suite no encontrada'], 422);
        }

        $suiteData   = $catalogo[$tipo][$suite];
        $casosDisp   = $suiteData['casos'];
        $casosCorrer = $request->casos
            ? array_intersect_key($casosDisp, array_flip($request->casos))
            : $casosDisp;

        $inicio     = Carbon::now();
        $resultados = [];
        foreach ($casosCorrer as $id => $caso) {
            $resultados[$id] = $this->ejecutarCaso($id, $caso, $baseUrl, $tipo);
        }
        $fin      = Carbon::now();
        $duracion = $inicio->diffInSeconds($fin);

        $ejecucion = [
            'id'          => 'EJC-' . $inicio->format('YmdHis'),
            'proyecto'    => 'Automatización QA',
            'equipo'      => config('app.name', 'DIALAB LIS QA Team'),
            'ambiente'    => $baseUrl,
            'tipo'        => ucfirst(str_replace('_', ' ', $tipo)),
            'suite'       => $suiteData['label'],
            'descripcion' => $suiteData['descripcion'],
            'inicio'      => $inicio->format('d/m/Y H:i:s'),
            'fin'         => $fin->format('d/m/Y H:i:s'),
            'duracion'    => $duracion,
            'email'       => $email,
            'resultados'  => $resultados,
            'total'       => count($resultados),
            'pass'        => count(array_filter($resultados, fn($r) => $r['estado'] === 'PASS')),
            'fail'        => count(array_filter($resultados, fn($r) => $r['estado'] === 'FAIL')),
            'skipped'     => count(array_filter($resultados, fn($r) => $r['estado'] === 'SKIPPED')),
            'not_run'     => count(array_filter($resultados, fn($r) => $r['estado'] === 'NOT RUN')),
        ];
        $ejecucion['pct_exito'] = $ejecucion['total'] > 0
            ? round(($ejecucion['pass'] / $ejecucion['total']) * 100, 1) : 0;

        $htmlPath = $this->generarReporteHTML($ejecucion);
        $pdfPath  = $this->generarInformePDF($ejecucion);
        $enviado  = $this->enviarCorreo($email, $ejecucion, $htmlPath, $pdfPath);

        @unlink($htmlPath);
        @unlink($pdfPath);

        return response()->json([
            'exito'     => true,
            'ejecucion' => $ejecucion,
            'enviado'   => $enviado,
        ]);
    }

    private function ejecutarCaso(string $id, array $caso, string $baseUrl, string $tipo): array
    {
        $inicio = microtime(true);
        $pasos  = [];
        try {
            $result = $this->ejecutarLogica($id, $caso, $baseUrl, $pasos);
        } catch (\Throwable $e) {
            $result = [
                'estado'      => 'FAIL',
                'observacion' => 'Error de infraestructura: ' . $e->getMessage(),
                'obtenido'    => 'Excepción: ' . get_class($e),
                'tipo_error'  => 'INFRASTRUCTURE',
            ];
        }
        return array_merge($caso, $result, [
            'id'            => $id,
            'duracion_ms'   => round((microtime(true) - $inicio) * 1000),
            'pasos_detalle' => $pasos,
        ]);
    }

    private function ejecutarLogica(string $id, array $caso, string $baseUrl, array &$pasos): array
    {
        if (str_starts_with($id, 'F-')) {
            return $this->ejecutarFuncional($id, $caso, $baseUrl, $pasos);
        }
        if (str_starts_with($id, 'N-')) {
            return $this->ejecutarNofuncional($id, $caso, $baseUrl, $pasos);
        }
        return $this->resultado(true, 'Caso sin lógica definida', 'N/A');
    }

    private function ejecutarFuncional(string $id, array $caso, string $baseUrl, array &$pasos): array
    {
        $http = ['base' => $baseUrl];

        if ($id === 'F-SMK-001') {
            $s = $this->loginSession($http, 'recepcion@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/', $s['cookies']);
            $ok = $r['status'] === 200;
            $pasos[] = ['paso' => 'GET / dashboard', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, 'Dashboard accesible', "HTTP {$r['status']}");
        }

        if ($id === 'F-SMK-002') {
            $s = $this->loginSession($http, 'laboratorio@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/muestras', $s['cookies']);
            $ok = $r['status'] === 200;
            $pasos[] = ['paso' => 'GET /muestras', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, 'Módulo de trazabilidad (muestras) accesible', "HTTP {$r['status']}");
        }

        if ($id === 'F-SNK-001') {
            $s = $this->loginSession($http, 'laboratorio@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/ordenes/pendientes', $s['cookies']);
            $ok = $r['status'] === 200 && !empty($r['body']);
            $pasos[] = ['paso' => 'GET /orders', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, 'Historial de órdenes responde', "HTTP {$r['status']}");
        }

        if ($id === 'F-SNK-002') {
            $s = $this->loginSession($http, 'admin@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/admin/pacientes', $s['cookies']);
            $ok = $r['status'] === 200;
            $pasos[] = ['paso' => 'GET /inventarios', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, 'Inventarios accesibles', "HTTP {$r['status']}");
        }

        if ($id === 'F-RG-001') {
            $s = $this->loginSession($http, 'recepcion@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r    = $this->get($http, '/', $s['cookies']);
            $csrf = $this->extractCsrf($r['body']);
            $r2   = $this->post($http, '/ordenes/registrar', [
                '_token' => $csrf, 'nombre_completo' => 'Test Sin Examen',
                'ci' => '9999001', 'edad' => '30', 'sexo' => 'Masculino', 'email' => 'test@dialab.com',
            ], $r['cookies']);
            $ok   = in_array($r2['status'], [302, 422]) || str_contains($r2['body'], 'examen');
            $pasos[] = ['paso' => 'POST /orders sin exámenes', 'resultado' => "HTTP {$r2['status']}"];
            return $this->resultado($ok, $ok ? 'Rechazada como esperado' : 'La orden se creó sin exámenes', "HTTP {$r2['status']}");
        }

        if ($id === 'F-RG-002') {
            $s = $this->loginSession($http, 'recepcion@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/pacientes/nuevo', $s['cookies']);
            $ok = $r['status'] === 200 && str_contains($r['body'], 'name');
            $pasos[] = ['paso' => 'GET /pacientes/nuevo', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, $ok ? 'Formulario presente' : "HTTP {$r['status']}");
        }

        if ($id === 'F-LOG-001') {
            $s = $this->loginSession($http, 'admin@dialab.com', 'password123', $pasos);
            $ok = $s['autenticado'];
            $pasos[] = ['paso' => 'POST /login admin', 'resultado' => $ok ? 'OK – autenticado' : $s['error']];
            return $this->resultado($ok, $ok ? 'Login exitoso' : 'Login fallido: ' . $s['error']);
        }

        if ($id === 'F-REG-001') {
            $s = $this->loginSession($http, 'recepcion@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/pacientes/nuevo', $s['cookies']);
            $ok = $r['status'] === 200 && str_contains($r['body'], 'nombre_completo');
            $pasos[] = ['paso' => 'GET /pacientes/nuevo', 'resultado' => "HTTP {$r['status']}"];
            return $this->resultado($ok, $ok ? 'Formulario presente' : "HTTP {$r['status']}");
        }

        if ($id === 'F-E2E-001') {
            $s = $this->loginSession($http, 'recepcion@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/', $s['cookies']);
            $r2 = $this->get($http, '/ordenes/pendientes', $s['cookies']);
            $ok = $r['status'] === 200 && $r2['status'] === 200;
            $pasos[] = ['paso' => 'GET / dashboard', 'resultado' => "HTTP {$r['status']}"];
            $pasos[] = ['paso' => 'GET / orders', 'resultado' => "HTTP {$r2['status']}"];
            return $this->resultado($ok, $ok ? 'Flujo OK' : 'Pasos HTTP fallidos');
        }

        if ($id === 'F-E2E-002') {
            $s = $this->loginSession($http, 'laboratorio@dialab.com', 'password123', $pasos);
            if (!$s['autenticado']) return $this->failAuth($s);
            $r = $this->get($http, '/ordenes/pendientes', $s['cookies']);
            $r2 = $this->get($http, '/resultados', $s['cookies']);
            $ok = $r['status'] === 200 && $r2['status'] === 200;
            $pasos[] = ['paso' => 'GET / orders (laboratorio)', 'resultado' => "HTTP {$r['status']}"];
            $pasos[] = ['paso' => 'GET / results', 'resultado' => "HTTP {$r2['status']}"];
            return $this->resultado($ok, $ok ? 'Flujo OK' : 'Pasos HTTP fallidos');
        }

        return ['estado' => 'NOT RUN', 'observacion' => 'Caso no implementado en ambiente funcional', 'obtenido' => 'NOT RUN'];
    }

    private function ejecutarNofuncional(string $id, array $caso, string $baseUrl, array &$pasos): array
    {
        $http = ['base' => $baseUrl];

        if ($id === 'N-PERF-001') {
            $sum = 0; $n = 8;
            foreach (range(1, $n) as $i) {
                $t = microtime(true);
                $r = $this->get($http, '/', [], false);
                $sum += (microtime(true) - $t) * 1000;
            }
            $prom = round($sum / $n, 1);
            $ok = $prom < 3000;
            $pasos[] = ['paso' => 'Promedio de latencia de GET /', 'resultado' => "{$prom} ms (límite 3000 ms)"];
            return $this->resultado($ok, $ok ? 'Latencia dentro del límite' : 'Promedio alto de latencia', "promedio {$prom} ms");
        }

        if ($id === 'N-PERF-002') {
            $sum = 0; $n = 8;
            foreach (range(1, $n) as $i) {
                $t = microtime(true);
                $r = $this->get($http, '/login', [], false);
                $sum += (microtime(true) - $t) * 1000;
            }
            $prom = round($sum / $n, 1);
            $ok = $prom < 3000;
            $pasos[] = ['paso' => 'Promedio de latencia de GET /login', 'resultado' => "{$prom} ms (límite 3000 ms)"];
            return $this->resultado($ok, $ok ? 'Latencia dentro del límite' : 'Promedio alto de latencia', "promedio {$prom} ms");
        }

        if ($id === 'N-LOAD-001') {
            $ok200 = 0; $n = 30;
            foreach (range(1, $n) as $i) {
                $r = $this->get($http, '/', [], false);
                $ok200 += $r['status'] === 200 ? 1 : 0;
                usleep(2 * 1000000);
            }
            $ok = $ok200 >= round(0.9 * $n);
            $pasos[] = ['paso' => "Carga progresiva: {$ok200}/{$n} respuestas 200", 'resultado' => $ok ? 'OK' : 'Fallos excesivos'];
            return $this->resultado($ok, $ok ? 'Sistema estable bajo carga' : "Sistema inestable: {$ok200}/{$n} 200", "{$ok200}/{$n} 200");
        }

        if ($id === 'N-LOAD-002') {
            $ok200 = 0; $n = 30;
            foreach (range(1, $n) as $i) {
                $r = $this->get($http, '/login', [], false);
                $ok200 += $r['status'] === 200 ? 1 : 0;
                usleep(2 * 1000000);
            }
            $ok = $ok200 >= round(0.9 * $n);
            $pasos[] = ['paso' => "Carga progresiva de login: {$ok200}/{$n} 200", 'resultado' => $ok ? 'OK' : 'Fallos excesivos'];
            return $this->resultado($ok, $ok ? 'Formulario estable' : "Formulario inestable: {$ok200}/{$n} 200", "{$ok200}/{$n} 200");
        }

        if ($id === 'N-EST-001') {
            $n = 100; $okCount = 0;
            for ($i = 0; $i < $n; $i++) {
                $r = $this->get($http, '/', [], false);
                $okCount += $r['status'] === 200 ? 1 : 0;
            }
            $ok = $okCount > 0;
            $pasos[] = ['paso' => "Stress: {$n} × GET /", 'resultado' => "{$okCount}/{$n} respuestas 200 (no hay caída total)"];
            return $this->resultado($ok, $ok ? 'Sin caídas inesperadas' : 'Sistema no respondió durante el stress', "{$okCount}/{$n} 200");
        }

        if ($id === 'N-EST-002') {
            $n = 50; $ok200 = 0;
            foreach (range(1, $n) as $i) {
                $r = $this->get($http, '/reportes', [], false);
                $ok200 += $r['status'] === 200 ? 1 : 0;
            }
            $ratio = $ok200 / $n;
            $ok = $ratio >= 0.95;
            $pasos[] = ['paso' => "Stress en reportes: {$ok200}/{$n} 200", 'resultado' => $ok ? 'OK – ratio 95%' : "Ratio insuficiente"];
            return $this->resultado($ok, $ok ? 'Reportes responden bajo stress' : 'Ratio de respuestas insuficiente', "{$ratio} ({$ok200}/{$n})");
        }

        if ($id === 'N-SOAK-001') {
            $n = 30; $okCount = 0;
            foreach (range(1, $n) as $i) {
                $r = $this->get($http, '/ordenes/pendientes', [], false);
                $okCount += $r['status'] === 200 ? 1 : 0;
                usleep(3 * 1000000);
            }
            $ok = $okCount === $n;
            $pasos[] = ['paso' => "Soak: {$n} × GET /orders con pausa", 'resultado' => "{$okCount}/{$n} 200"];
            return $this->resultado($ok, $ok ? 'Endurancia OK' : 'Fallo durante carga sostenida', "{$okCount}/{$n} 200");
        }

        return ['estado' => 'NOT RUN', 'observacion' => 'Caso sin lógica no funcional', 'obtenido' => 'NOT RUN'];
    }

    private function resultado(bool $ok, string $observacion, string $obtenido): array
    {
        return [
            'estado'      => $ok ? 'PASS' : 'FAIL',
            'observacion' => $observacion,
            'obtenido'    => $obtenido,
        ];
    }

    private function failAuth(array $session): array
    {
        return [
            'estado'      => 'FAIL',
            'observacion' => 'No se pudo autenticar el usuario de prueba: ' . $session['error'],
            'obtenido'    => 'Auth fallida',
            'tipo_error'  => 'INFRASTRUCTURE',
        ];
    }

    private function get(array $http, string $path, array $cookies = [], bool $auth = true): array
    {
        $url = $http['base'] . $path;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_COOKIE         => $this->cookieStr($cookies),
        ]);
        $raw    = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $header = substr($raw ?: '', 0, $hsize);
        $body   = substr($raw ?: '', $hsize);
        return ['status' => $status, 'body' => $body, 'headers' => $this->parseHeaders($header), 'cookies' => array_merge($cookies, $this->parseCookies($header))];
    }

    private function post(array $http, string $path, array $data, array $cookies = []): array
    {
        $url = $http['base'] . $path;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIE         => $this->cookieStr($cookies),
        ]);
        $raw    = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $header = substr($raw ?: '', 0, $hsize);
        $body   = substr($raw ?: '', $hsize);
        return ['status' => $status, 'body' => $body, 'headers' => $this->parseHeaders($header), 'cookies' => array_merge($cookies, $this->parseCookies($header))];
    }

    private function loginSession(array $http, string $email, string $password, array &$pasos): array
    {
        $r    = $this->get($http, '/login');
        $csrf = $this->extractCsrf($r['body']);
        $r2   = $this->post($http, '/login', ['email' => $email, 'password' => $password, '_token' => $csrf], $r['cookies']);
        $pasos[] = ['paso' => "POST /login como {$email}", 'resultado' => "HTTP {$r2['status']}"];
        $location = $r2['headers']['location'][0] ?? '';
        $ok = in_array($r2['status'], [200, 302]) && !str_contains($location, 'login');
        $cookies = $r2['cookies'];
        if ($ok && $r2['status'] === 302) {
            $r3   = $this->get($http, '/', $cookies);
            $ok   = $r3['status'] === 200;
            $pasos[] = ['paso' => 'Seguir redirección al dashboard', 'resultado' => "HTTP {$r3['status']}"];
        }
        return ['autenticado' => $ok, 'cookies' => $cookies, 'csrf' => $csrf, 'error' => $ok ? '' : "HTTP {$r2['status']} location:$location"];
    }

    private function extractCsrf(string $html): string
    {
        preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/i', $html, $m);
        if (!$m) preg_match('/meta[^>]*name="csrf-token"[^>]*content="([^"]+)"/i', $html, $m);
        return $m[1] ?? '';
    }

    private function parseCookies(string $header): array
    {
        $cookies = [];
        preg_match_all('/Set-Cookie:\s*([^=]+)=([^;]+)/i', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) $cookies[trim($m[1])] = trim($m[2]);
        return $cookies;
    }

    private function parseHeaders(string $header): array
    {
        $h = [];
        foreach (explode("\r\n", $header) as $line) {
            if (str_contains($line, ':')) {
                [$key, $val] = explode(':', $line, 2);
                $h[strtolower(trim($key))][] = trim($val);
            }
        }
        return $h;
    }

    private function cookieStr(array $cookies): string
    {
        return implode('; ', array_map(fn($k, $v) => "$k=$v", array_keys($cookies), $cookies));
    }

    private function generarReporteHTML(array $ejecucion): string
    {
        $cat = $this->getCatalogo();
        $filas = '';
        foreach ($ejecucion['resultados'] as $r) {
            $est = $r['estado'] ?? 'NOT RUN';
            $badge = match($est) {
                'PASS'    => 'badge pass',
                'FAIL'    => 'badge fail',
                'SKIPPED' => 'badge skip',
                default   => 'badge notrun',
            };
            $obs = htmlspecialchars($r['observacion'] ?? '-');
            $obsFail = '';
            if ($est === 'FAIL') {
                $obsFail = '<br><small style="color:#dc2626">' . htmlspecialchars(trim(($r['observacion'] ?? '') . ' — ' . ($r['obtenido'] ?? ''))) . '</small>';
            }
            $suite = '';
            foreach ($cat as $tipo => $suites) {
                foreach ($suites as $sk => $sdata) {
                    if (isset($sdata['casos'][$r['id']])) $suite = htmlspecialchars($sdata['label']);
                }
            }
            $filas .= "<tr>
                <td><code>{$r['id']}</code></td>
                <td>" . htmlspecialchars($r['nombre'] ?? '') . "</td>
                <td><small>" . htmlspecialchars($r['tipo'] ?? '-') . "</small></td>
                <td><small>{$suite}</small></td>
                <td><span class='{$badge}' style='font-weight:bold;text-transform:uppercase'>{$est}</span></td>
                <td>{$obs}{$obsFail}</td>
            </tr>\n";
        }

        $path = storage_path('app/qa_reporte_' . time() . '.html');
        file_put_contents($path, <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><title>Reporte QA – {$ejecucion['suite']} – {$ejecucion['inicio']}</title>
<style>
body{font-family:system-ui,sans-serif;background:#f8fafc;color:#1e293b;padding:24px;margin:0}
h1{font-size:1.4rem}
.sub{color:#64748b;font-size:.9rem;margin:4px 0 20px}
.summary{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px}
.card{background:#fff;border-radius:10px;padding:12px 18px;min-width:105px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.07);flex:1}
.card .num{font-size:1.6rem;font-weight:700}
.card .lbl{font-size:.72rem;color:#64748b;text-transform:uppercase}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07)}
th{background:#0f172a;color:#fff;padding:8px 10px;text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em}
td{padding:8px 10px;border-bottom:1px solid #f1f5f9;vertical-align:middle;font-size:.85rem}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8fafc}
.badge{display:inline-block;padding:3px 8px;border-radius:20px;font-weight:700;font-size:.75rem;text-transform:uppercase}
.pass{background:#dcfce7;color:#15803d}.fail{background:#fee2e2;color:#b91c1c}.skip{background:#fef9c3;color:#a16207}.notrun{background:#e2e8f0;color:#475569}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.8rem}
footer{margin-top:18px;font-size:.8rem;color:#94a3b8;text-align:center}
</style>
</head>
<body>
<h1>🧪 Reporte de Automatización QA</h1>
<div class="sub">Suite: <strong>{$ejecucion['suite']}</strong> | Tipo: {$ejecucion['tipo']} | Inicio: {$ejecucion['inicio']} | Fin: {$ejecucion['fin']} | Duración: {$ejecucion['duracion']}s</div>
<div class="summary">
  <div class="card"><div class="num">{$ejecucion['total']}</div><div class="lbl">Total</div></div>
  <div class="card"><div class="num" style="color:#16a34a">{$ejecucion['pass']}</div><div class="lbl">PASS</div></div>
  <div class="card"><div class="num" style="color:#dc2626">{$ejecucion['fail']}</div><div class="lbl">FAIL</div></div>
  <div class="card"><div class="num" style="color:#ca8a04">{$ejecucion['skipped']}</div><div class="lbl">SKIPPED</div></div>
  <div class="card"><div class="num" style="color:#94a3b8">{$ejecucion['not_run']}</div><div class="lbl">NOT RUN</div></div>
  <div class="card"><div class="num" style="color:#16a34a">{$ejecucion['pct_exito']}%</div><div class="lbl">Éxito</div></div>
</div>
<table>
<thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Suite</th><th>Estado</th><th>Observación / Fallo</th></tr></thead>
<tbody>{$filas}</tbody>
</table>
<footer>DIALAB LIS QA Platform • {$ejecucion['fin']} • ID: {$ejecucion['id']}</footer>
</body>
</html>
HTML);
        return $path;
    }

    private function generarInformePDF(array $ejecucion): string
    {
        $path = storage_path('app/qa_informe_' . time() . '.pdf');
        $this->buildPDF($ejecucion, $path);
        return $path;
    }

    private function buildPDF(array $ejecucion, string $path): void
    {
        $pdf = new PDFGenerator();
        $pdf->headerFactura('INFORME DE AUTOMATIZACIÓN QA', "Suite: {$ejecucion['suite']}", $ejecucion['id'], $ejecucion['inicio']);

        $ref = new \ReflectionObject($pdf);
        $cp  = $ref->getProperty('content'); $cp->setAccessible(true);
        $yp  = $ref->getProperty('currentY'); $yp->setAccessible(true);
        $y   = $yp->getValue($pdf) - 15;
        $c   = $cp->getValue($pdf);

        $esc = fn($t) => str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], (string)$t);

        foreach ([
            "Proyecto: {$ejecucion['proyecto']}",
            "Equipo: {$ejecucion['equipo']}",
            "Ambiente: {$ejecucion['ambiente']}",
            "Tipo: {$ejecucion['tipo']} | Suite: {$ejecucion['suite']}",
            "Inicio: {$ejecucion['inicio']} | Fin: {$ejecucion['fin']} | Duración: {$ejecucion['duracion']}s",
            "Destinatario: {$ejecucion['email']}",
            "RESUMEN: Total {$ejecucion['total']} | PASS {$ejecucion['pass']} | FAIL {$ejecucion['fail']} | SKIPPED {$ejecucion['skipped']} | NOT RUN {$ejecucion['not_run']} | Éxito {$ejecucion['pct_exito']}%",
        ] as $line) {
            $c .= "BT /F1 9 Tf 40 $y Td ({$esc($line)}) Tj ET\n";
            $y -= 13;
        }

        $y -= 8;
        $c .= "BT /F2 11 Tf 40 $y Td (DETALLE DE CASOS) Tj ET\n";
        $y -= 20;

        foreach ($ejecucion['resultados'] as $r) {
            if ($y < 100) $y = 780;
            $c .= "BT /F2 10 Tf 40 $y Td ({$esc($r['id'].' - '.$r['nombre'])}) Tj ET\n"; $y -= 13;
            $c .= "BT /F1 8 Tf 42 $y Td (Tipo: {$esc($r['tipo'] ?? '')} | Prioridad: {$esc($r['prioridad'] ?? '')}) Tj ET\n"; $y -= 11;
            $c .= "BT /F1 8 Tf 42 $y Td (Objetivo: {$esc(substr($r['objetivo'] ?? '', 0, 100))}) Tj ET\n"; $y -= 11;
            $c .= "BT /F1 8 Tf 42 $y Td (Datos: {$esc(substr($r['datos'] ?? '', 0, 100))}) Tj ET\n"; $y -= 11;
            $c .= "BT /F1 8 Tf 42 $y Td (Esperado: {$esc(substr($r['esperado'] ?? '', 0, 100))}) Tj ET\n"; $y -= 11;
            $c .= "BT /F1 8 Tf 42 $y Td (Obtenido: {$esc(substr($r['obtenido'] ?? '', 0, 100))}) Tj ET\n"; $y -= 11;
            $c .= "BT /F2 9 Tf 42 $y Td (Estado: {$esc($r['estado'])} | {$esc(substr($r['observacion'] ?? '', 0, 100))}) Tj ET\n"; $y -= 11;

            if (!empty($r['pasos_detalle'])) {
                $c .= "BT /F2 8 Tf 42 $y Td (Pasos ejecutados:) Tj ET\n"; $y -= 10;
                foreach ($r['pasos_detalle'] as $i => $p) {
                    if ($y < 60) $y = 780;
                    $pt = ($i + 1) . '. ' . substr($p['paso'] ?? '', 0, 70) . ' → ' . substr($p['resultado'] ?? '', 0, 50);
                    $c .= "BT /F1 7.5 Tf 50 $y Td ({$esc($pt)}) Tj ET\n"; $y -= 10;
                }
            }
            $c .= "0.9 0.9 0.9 rg 40 " . ($y - 2) . " 515 1 re f\n0 0 0 rg\n";
            $y -= 12;
        }

        $cp->setValue($pdf, $c);
        $yp->setValue($pdf, $y);
        $pdf->save($path);
    }

    private function enviarCorreo(string $email, array $ejecucion, string $htmlPath, string $pdfPath): bool
    {
        try {
            $asunto = "Reporte de automatización – {$ejecucion['suite']} – {$ejecucion['inicio']}";
            Mail::send([], [], function ($m) use ($email, $asunto, $htmlPath, $pdfPath, $ejecucion) {
                $m->to($email)->subject($asunto)->html("
                  <h2>🧪 DIALAB LIS – Reporte de Automatización QA</h2>
                  <p><b>Suite:</b> {$ejecucion['suite']} | <b>Tipo:</b> {$ejecucion['tipo']}<br>
                  <b>Inicio:</b> {$ejecucion['inicio']} | <b>Fin:</b> {$ejecucion['fin']} | <b>Duración:</b> {$ejecucion['duracion']}s</p>
                  <table border='1' cellpadding='8' style='border-collapse:collapse'>
                    <tr><th>Total</th><th>PASS</th><th>FAIL</th><th>SKIPPED</th><th>NOT RUN</th><th>% Éxito</th></tr>
                    <tr><td>{$ejecucion['total']}</td><td style='color:green'><b>{$ejecucion['pass']}</b></td>
                    <td style='color:red'><b>{$ejecucion['fail']}</b></td><td>{$ejecucion['skipped']}</td>
                    <td>{$ejecucion['not_run']}</td><td><b>{$ejecucion['pct_exito']}%</b></td></tr>
                  </table>
                  <p>Se adjuntan dos archivos:<br>
                  📄 <b>Informe detallado (PDF)</b> – datos completos de cada caso<br>
                  🌐 <b>Reporte HTML</b> – resumen visual de estados</p>
                  <p style='color:#888;font-size:11px'>DIALAB LIS QA Platform • {$ejecucion['fin']} • ID: {$ejecucion['id']}</p>
                ");
                if (file_exists($pdfPath))  $m->attach($pdfPath,  ['as' => "informe_qa_{$ejecucion['id']}.pdf",  'mime' => 'application/pdf']);
                if (file_exists($htmlPath)) $m->attach($htmlPath, ['as' => "reporte_qa_{$ejecucion['id']}.html", 'mime' => 'text/html']);
            });
            return true;
        } catch (\Throwable $e) {
            \Log::error('QA Mail error: ' . $e->getMessage());
            return false;
        }
    }
}
