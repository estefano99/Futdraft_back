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
        Schema::table('users', function (Blueprint $table) {

            $table->string('nombre');
            $table->string('apellido');
            $table->string('dni')->unique();
            $table->string('nro_celular');
            $table->enum('tipo_usuario', ['cliente', 'admin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'apellido', 'dni', 'nro_Celular', 'tipoUsuario', 'password']);
        });
    }
};
