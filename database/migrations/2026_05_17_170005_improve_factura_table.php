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
        Schema::table('Factura', function (Blueprint $table) {
            $table->unsignedBigInteger('id_reservarviajes')->nullable()->after('id_precioviajes');
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('impuesto', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('numero_factura')->unique()->nullable();
            $table->timestamp('fecha_emision')->useCurrent();

            $table->foreign('id_reservarviajes')->references('id_reservarviajes')->on('reservarviajes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Factura', function (Blueprint $table) {
            $table->dropForeign(['id_reservarviajes']);
            $table->dropColumn(['id_reservarviajes', 'subtotal', 'impuesto', 'total', 'numero_factura', 'fecha_emision']);
        });
    }
};
