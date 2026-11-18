<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('accion'); // Ej: Registro paciente, Validación resultado
            $table->string('tabla_afectada')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('detalle')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('auditoria_logs');
    }
};
