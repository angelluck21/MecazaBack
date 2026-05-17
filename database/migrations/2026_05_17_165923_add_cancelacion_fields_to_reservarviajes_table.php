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
        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->change();
            $table->text('motivo_cancelacion')->nullable();
            $table->enum('cancelado_por', ['usuario', 'conductor', 'admin'])->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->dropColumn(['motivo_cancelacion', 'cancelado_por', 'fecha_cancelacion']);
        });
    }
};
