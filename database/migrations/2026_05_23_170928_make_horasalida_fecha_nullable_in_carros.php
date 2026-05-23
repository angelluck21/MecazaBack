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
            $table->string('horasalida')->nullable()->change();
            $table->date('fecha')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('carros', function (Blueprint $table) {
            $table->string('horasalida')->nullable(false)->change();
            $table->date('fecha')->nullable(false)->change();
        });
    }
};
