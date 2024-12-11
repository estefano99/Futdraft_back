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
        Schema::create('acciones', function (Blueprint $table) {
            $table->id('acc_id'); // Primary key
            $table->string('acc_nombre')->unique();
            $table->foreignId('for_id')->constrained('formularios')->onDelete('cascade'); // Foreign key
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acciones');
    }
};
