<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model {
    protected $table = 'resultados';
    protected $fillable = ['muestra_id', 'resultado', 'valores_referencia', 'interpretacion', 'bioquimico_validador_id', 'fecha_validacion', 'estado', 'observaciones_validacion'];

    protected $casts = [
        'fecha_validacion' => 'datetime',
    ];

    public function muestra(): BelongsTo {
        return $this->belongsTo(Muestra::class, 'muestra_id');
    }

    public function bioquimico(): BelongsTo {
        return $this->belongsTo(\App\Models\User::class, 'bioquimico_validador_id');
    }
}
