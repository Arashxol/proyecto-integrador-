<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function usuarios()
    {
        $usuarios = User::orderBy('rol')->orderBy('name')->get();
        $logs = AuditoriaLog::with('user')->latest()->take(20)->get();
        return view('admin.usuarios', compact('usuarios', 'logs'));
    }

    public function crearUsuario(Request $request)
    {
        $validacion = Validaciones::validarUsuario($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $datos = $validacion['datos'];

        $user = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol' => $datos['rol'],
            'activo' => $datos['activo'],
        ]);
        AuditoriaLog::registrar('Creación usuario', 'users', $user->id, "Rol: {$datos['rol']} Email: {$datos['email']}");
        return redirect()->back()->with('success', "Usuario {$datos['name']} ({$datos['rol']}) creado.");
    }

    public function toggleActivo($id)
    {
        $user = User::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();
        AuditoriaLog::registrar($user->activo ? 'Activación usuario' : 'Desactivación usuario', 'users', $user->id);
        return redirect()->back()->with('success', 'Estado actualizado.');
    }

    public function pacientes()
    {
        $pacientes = \App\Models\Paciente::withCount('ordenes')->orderBy('nombre_completo')->paginate(15);
        return view('admin.pacientes', compact('pacientes'));
    }

    public function logs(Request $request)
    {
        $query = AuditoriaLog::with('user')->latest();
        if ($request->filled('accion')) {
            $query->where('accion', 'like', '%'.$request->accion.'%');
        }
        if ($request->filled('usuario')) {
            $query->whereHas('user', fn($q) => $q->where('name','like','%'.$request->usuario.'%')->orWhere('email','like','%'.$request->usuario.'%'));
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
        $logs = $query->paginate(25)->withQueryString();
        return view('admin.logs', compact('logs'));
    }

    public function backups()
    {
        $dir = storage_path('app/backups');
        $files = [];
        if (is_dir($dir)) {
            $files = collect(scandir($dir))
                ->filter(fn($f) => str_ends_with($f, '.sql') || str_ends_with($f, '.gz') || str_ends_with($f, '.json'))
                ->map(fn($f) => [
                    'name' => $f,
                    'size' => round(filesize($dir.DIRECTORY_SEPARATOR.$f)/1024, 2).' KB',
                    'date' => date('Y-m-d H:i:s', filemtime($dir.DIRECTORY_SEPARATOR.$f)),
                ])
                ->sortByDesc('date')
                ->values();
        }
        return view('admin.backups', compact('files'));
    }

    public function generarBackup(Request $request)
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $filename = 'backup_laboratorio_pii_'.date('Y-m-d_H-i-s').'.sql';
        $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

        $db = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $dumpPaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump',
            '/opt/lampp/bin/mysqldump',
            '/usr/bin/mysqldump',
            'mysqldump',
        ];

        $mysqldump = null;
        foreach ($dumpPaths as $p) {
            if ($p === 'mysqldump') { $mysqldump = $p; break; }
            if (file_exists($p)) { $mysqldump = '"'.$p.'"'; break; }
        }

        $success = false;
        $msg = '';

        if ($mysqldump) {
            $passArg = $pass ? "-p\"{$pass}\"" : "";
            $cmd = "{$mysqldump} -h {$host} -P {$port} -u {$user} {$passArg} {$db} --routines --events --single-transaction 2>&1 > \"{$filepath}\"";
            @exec($cmd, $output, $code);
            if ($code === 0 && file_exists($filepath) && filesize($filepath) > 100) {
                $success = true;
                $msg = "Backup SQL generado correctamente.";
            }
        }

        if (!$success) {
            try {
                $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
                $key = 'Tables_in_'.$db;
                $data = [];
                foreach ($tables as $t) {
                    $table = $t->$key ?? array_values((array)$t)[0];
                    $data[$table] = \Illuminate\Support\Facades\DB::table($table)->get()->toArray();
                }
                $jsonFile = str_replace('.sql', '.json', $filepath);
                file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $filename = basename($jsonFile);
                $filepath = $jsonFile;
                $success = true;
                $msg = "mysqldump no disponible, se generó respaldo JSON.";
            } catch (\Exception $e) {
                $msg = "Error al generar backup: ".$e->getMessage();
            }
        }

        AuditoriaLog::registrar($success ? 'Generación de backup' : 'Intento fallido de backup', 'backups', null, $filename.' - '.$msg);

        if ($success) {
            return redirect()->route('admin.backups')->with('success', $msg.' Archivo: '.$filename.' ('.round(filesize($filepath)/1024,2).' KB)');
        }
        return redirect()->back()->withErrors(['backup' => $msg]);
    }

    public function descargarBackup($file)
    {
        $path = storage_path('app/backups'.DIRECTORY_SEPARATOR.basename($file));
        if (!file_exists($path)) abort(404, 'Backup no encontrado');
        AuditoriaLog::registrar('Descarga de backup', 'backups', null, $file);
        return response()->download($path);
    }
}
