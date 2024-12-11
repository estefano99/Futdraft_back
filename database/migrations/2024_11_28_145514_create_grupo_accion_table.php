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
        Schema::create('grupo_accion', function (Blueprint $table) {
            $table->foreignId('gru_id')->constrained('grupos')->onDelete('cascade'); // Foreign key
            $table->foreignId('acc_id')->constrained('acciones')->onDelete('cascade'); // Foreign key
            $table->timestamps();

            $table->primary(['gru_id', 'acc_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_accion');
    }
};
