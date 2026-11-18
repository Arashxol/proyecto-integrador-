<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_orden')->unique(); // Código único autogenerado
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('medico_solicitante')->nullable();
            $table->text('diagnostico_presuntivo')->nullable();
            $table->date('fecha_solicitud');
            $table->date('fecha_entrega_estimada');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Completada', 'Cancelada'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ordenes_analisis');
    }
};