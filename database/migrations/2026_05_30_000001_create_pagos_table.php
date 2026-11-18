<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes_analisis')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['Pendiente', 'Pagado', 'Parcial'])->default('Pendiente');
            $table->enum('metodo', ['Efectivo', 'Transferencia', 'QR', 'Tarjeta'])->default('Efectivo');
            $table->foreignId('cajero_id')->nullable()->constrained('users');
            $table->dateTime('fecha_pago')->nullable();
            $table->string('comprobante')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pagos');
    }
};
