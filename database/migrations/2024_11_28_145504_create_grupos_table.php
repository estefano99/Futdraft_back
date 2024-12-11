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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id('gru_id'); // Primary key
            $table->string('gru_nombre')->unique();
            $table->text('gru_descripcion')->nullable();
            $table->foreignId('est_gru_id')->constrained('estado_grupos')->onDelete('cascade'); // Foreign key
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
