<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del proyecto
            $table->text('description')->nullable(); // Descripción opcional
            $table->enum('status', ['pendiente', 'en progreso', 'completado'])->default('pendiente'); // Estado
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de finalización
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
