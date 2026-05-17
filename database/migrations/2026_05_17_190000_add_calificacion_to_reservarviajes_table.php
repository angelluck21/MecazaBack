<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->tinyInteger('calificacion')->nullable();
            $table->text('comentario_calificacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->dropColumn(['calificacion', 'comentario_calificacion']);
        });
    }
};
