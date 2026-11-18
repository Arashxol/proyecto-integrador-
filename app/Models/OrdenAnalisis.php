<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdenAnalisis extends Model {
    protected $table = 'ordenes_analisis';
    protected $fillable = ['codigo_orden', 'paciente_id', 'medico_solicitante', 'diagnostico_presuntivo', 'fecha_solicitud', 'fecha_entrega_estimada', 'total', 'estado'];

    public function paciente(): BelongsTo {
        return $this->belongsTo(Paciente::class);
    }

    public function muestras(): HasMany {
        return $this->hasMany(Muestra::class, 'orden_id');
    }

    public function pago(): HasOne {
        return $this->hasOne(Pago::class, 'orden_id');
    }
}
