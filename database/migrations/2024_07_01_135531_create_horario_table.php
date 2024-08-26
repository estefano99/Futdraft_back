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
        Schema::create('horario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('horario_apertura');
            $table->time('horario_cierre');
            $table->time('duracion_turno');
            $table->unsignedBigInteger('cancha_id');
            $table->timestamps();

            // Definir la clave foránea
            $table->foreign('cancha_id')->references('id')->on('cancha')->onDelete('cascade');

            // Definir la restricción de unicidad compuesta
            $table->unique(['cancha_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};
