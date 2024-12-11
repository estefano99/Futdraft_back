<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoUsuarioToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Agrega la columna de estado_usuario y la relación con la tabla estado_usuario
            $table->unsignedBigInteger('estado_usuario_id')->nullable()->after('id');

            // Foreign key
            $table->foreign('estado_usuario_id')->references('id')->on('estado_usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la relación y la columna
            $table->dropForeign(['estado_usuario_id']);
            $table->dropColumn('estado_usuario_id');
        });
    }
}
