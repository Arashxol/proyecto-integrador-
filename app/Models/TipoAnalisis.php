<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAnalisis extends Model {
    protected $table = 'tipos_analisis';
    protected $fillable = ['nombre', 'valores_referencia', 'descripcion', 'precio', 'categoria', 'tiempo_entrega_horas', 'activo'];
}
