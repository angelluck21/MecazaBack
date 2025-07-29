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
        Schema::create('reservarviajes', function (Blueprint $table) {
            $table->id("id_reservarviajes");
            $table-> string ("regate");
            $table-> string ("comentario");
            $table-> string ("ubicacion");
            $table-> string ("asiento");
            $table-> unsignedBigInteger("id_users");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservarviajes');
    }
};
