<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model {
    protected $table = 'pacientes';
    protected $fillable = ['nombre_completo', 'ci', 'edad', 'sexo', 'direccion', 'telefono', 'email', 'fecha_registro', 'observaciones', 'activo'];

    public function ordenes(): HasMany {
        return $this->hasMany(OrdenAnalisis::class);
    }
}
