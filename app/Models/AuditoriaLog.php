<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaLog extends Model {
    protected $table = 'auditoria_logs';
    protected $fillable = ['user_id', 'accion', 'tabla_afectada', 'registro_id', 'detalle', 'ip'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public static function registrar($accion, $tabla = null, $registroId = null, $detalle = null) {
        return self::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'detalle' => $detalle,
            'ip' => request()->ip(),
        ]);
    }
}
