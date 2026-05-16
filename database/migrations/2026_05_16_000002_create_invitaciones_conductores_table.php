<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitaciones_conductores', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->boolean('usado')->default(false);
            $table->unsignedBigInteger('creado_por');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('creado_por')->references('id_users')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitaciones_conductores');
    }
};
