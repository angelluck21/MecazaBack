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
        Schema::table('carros', function (Blueprint $table) {
            $table->unsignedInteger('viaje_numero')->default(1)->after('id_estados');
        });

        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->unsignedInteger('viaje_numero')->default(1)->after('id_carros');
        });
    }

    public function down(): void
    {
        Schema::table('carros', function (Blueprint $table) {
            $table->dropColumn('viaje_numero');
        });

        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->dropColumn('viaje_numero');
        });
    }
};
