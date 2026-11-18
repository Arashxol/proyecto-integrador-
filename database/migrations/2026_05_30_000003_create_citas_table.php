<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // paciente que agenda si tiene cuenta
            $table->date('fecha');
            $table->time('hora');
            $table->string('tipo')->default('Toma de muestra'); // Toma de muestra, Consulta, Entrega resultados
            $table->enum('estado', ['Pendiente','Confirmada','Cancelada','Completada'])->default('Pendiente');
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('citas');
    }
};
