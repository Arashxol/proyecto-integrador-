<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Muestra extends Model {
    protected $table = 'muestras';
    protected $fillable = ['codigo_muestra', 'orden_id', 'tipo_analisis_id', 'tipo_muestra', 'estado', 'fecha_recoleccion', 'fecha_analisis', 'tecnico_asignado_id', 'observaciones'];

    public function orden(): BelongsTo {
        return $this->belongsTo(OrdenAnalisis::class, 'orden_id');
    }

    public function tipoAnalisis(): BelongsTo {
        return $this->belongsTo(TipoAnalisis::class, 'tipo_analisis_id');
    }

    public function resultado(): HasOne {
        return $this->hasOne(Resultado::class, 'muestra_id');
    }

    public function tecnico(): BelongsTo {
        return $this->belongsTo(\App\Models\User::class, 'tecnico_asignado_id');
    }
}
