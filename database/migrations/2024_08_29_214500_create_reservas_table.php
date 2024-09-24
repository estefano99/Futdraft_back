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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->decimal('precio', 8, 2);
            $table->timestamp('fecha');
            $table->unsignedBigInteger('cancha_id'); // Agregar la columna cancha_id
            $table->unsignedBigInteger('usuario_id'); // Agregar la columna usuario_id
            $table->timestamps();

            // Definir la clave foránea
            $table->foreign('cancha_id')->references('id')->on('cancha')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};

