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
        Schema::create('motivos_cancelacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_reservarviajes');
            $table->unsignedBigInteger('id_users');
            $table->text('motivo');
            $table->enum('tipo', ['usuario', 'conductor', 'admin'])->default('usuario');
            $table->timestamps();

            $table->foreign('id_reservarviajes')->references('id_reservarviajes')->on('reservarviajes')->onDelete('cascade');
            $table->foreign('id_users')->references('id_users')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivos_cancelacion');
    }
};
