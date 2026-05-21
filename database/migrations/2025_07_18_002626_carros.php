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
        Schema::create('carros', function (Blueprint $table) {
        $table->id('id_carros');
        $table->string('conductor');
        $table->string('imagencarro')->nullable();
        $table->string('telefono');
        $table->string('placa')->unique();
        $table->string('asientos');
        $table->string('horasalida');
        $table->date('fecha');
        $table->unsignedBigInteger('id_users');
        $table->unsignedBigInteger('id_estados');
        $table->unsignedBigInteger('id_precioviaje')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agregarcarros');
    }
};
