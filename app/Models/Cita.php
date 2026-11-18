<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model {
    protected $table = 'citas';
    protected $fillable = ['paciente_id','user_id','fecha','hora','tipo','estado','motivo','observaciones'];
    protected $casts = ['fecha' => 'date', 'hora' => 'datetime:H:i'];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
