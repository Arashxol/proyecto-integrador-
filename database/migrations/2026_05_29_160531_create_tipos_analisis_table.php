<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tipos_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Hemograma, Glucosa, Orina Completa
            $table->string('valores_referencia')->nullable(); // Ej: "Hombres: 13-17 g/dL, Mujeres: 12-15 g/dL"
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('categoria'); // Ej: Hematología, Bioquímica
            $table->integer('tiempo_entrega_horas')->default(24);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tipos_analisis');
    }
};
