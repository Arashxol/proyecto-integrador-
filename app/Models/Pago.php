<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model {
    protected $table = 'pagos';
    protected $fillable = ['orden_id', 'monto', 'estado', 'metodo', 'cajero_id', 'fecha_pago', 'comprobante'];

    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function orden(): BelongsTo {
        return $this->belongsTo(OrdenAnalisis::class, 'orden_id');
    }
    public function cajero(): BelongsTo {
        return $this->belongsTo(User::class, 'cajero_id');
    }
}
