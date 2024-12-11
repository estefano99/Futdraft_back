<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usuario_grupo', function (Blueprint $table) {
            $table->foreignId('usu_id')->constrained('users')->onDelete('cascade'); // Foreign key
            $table->foreignId('gru_id')->constrained('grupos')->onDelete('cascade'); // Foreign key
            $table->timestamps();

            $table->primary(['usu_id', 'gru_id']); // Composite primary key
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_grupo');
    }
};
