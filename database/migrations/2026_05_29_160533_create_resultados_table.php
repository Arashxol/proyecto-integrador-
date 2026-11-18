<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestra_id')->constrained('muestras')->onDelete('cascade');
            $table->text('resultado'); // Valores numéricos o reactivos
            $table->string('valores_referencia')->nullable(); // Rangos normales
            $table->text('interpretacion')->nullable();
            $table->foreignId('bioquimico_validador_id')->nullable()->constrained('users');
            $table->dateTime('fecha_validacion')->nullable();
            $table->enum('estado', ['Pendiente', 'Validado', 'Rechazado'])->default('Pendiente');
            $table->text('observaciones_validacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('resultados');
    }
};
