<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('muestras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_muestra')->unique(); // Código de barras / identificador único
            $table->foreignId('orden_id')->constrained('ordenes_analisis')->onDelete('cascade');
            $table->foreignId('tipo_analisis_id')->constrained('tipos_analisis')->onDelete('cascade');
            $table->enum('tipo_muestra', ['Sangre', 'Orina', 'Heces', 'Saliva', 'Tejido', 'Otro']);
            $table->enum('estado', ['Recolectado', 'En Transito', 'En Analisis', 'Resultado Listo', 'Archivada'])->default('Recolectado');
            $table->dateTime('fecha_recoleccion');
            $table->dateTime('fecha_analisis')->nullable();
            $table->foreignId('tecnico_asignado_id')->nullable()->constrained('users'); // Quién la procesa
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('muestras');
    }
};
