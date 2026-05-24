<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar duplicados antes de aplicar el índice único:
        // conserva solo la factura más reciente por reserva.
        \DB::statement("
            DELETE f1
            FROM Factura f1
            INNER JOIN Factura f2
                ON f1.id_reservarviajes = f2.id_reservarviajes
               AND f1.id_factura < f2.id_factura
            WHERE f1.id_reservarviajes IS NOT NULL
        ");

        Schema::table('Factura', function (Blueprint $table) {
            $table->unique('id_reservarviajes', 'factura_reserva_unique');
        });
    }

    public function down(): void
    {
        Schema::table('Factura', function (Blueprint $table) {
            $table->dropUnique('factura_reserva_unique');
        });
    }
};
