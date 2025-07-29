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
        Schema::create('Factura', function (Blueprint $table) {
            $table->id("id_factura");
            $table-> unsignedBigInteger("id_users");
            $table->string("destino");
            $table-> unsignedBigInteger ("id_carros");
            $table-> unsignedBigInteger ("id_precioviajes");
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
