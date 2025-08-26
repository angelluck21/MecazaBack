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
        Schema::create('agregarcarros', function (Blueprint $table) {
        $table->id("id_carros");
        $table-> string ("conductor");
        $table-> string ("imagencarro");
        $table-> string ("telefono");
        $table-> string ("placa");
        $table-> string ("asientos");
        $table-> string ("destino");
        $table-> string ("horasalida");
        $table-> unsignedBigInteger("id_users");
        $table-> unsignedBigInteger("id_estados");
        $table-> date ("fecha");
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
