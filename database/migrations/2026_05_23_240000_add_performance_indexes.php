<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── reservarviajes ───────────────────────────────────────────────────
        Schema::table('reservarviajes', function (Blueprint $table) {
            // WHERE id_carros = ? — aparece en casi todas las consultas
            $table->index('id_carros', 'idx_rv_id_carros');

            // WHERE id_users = ? — mis-reservas-usuario, calificar, destroy
            $table->index('id_users', 'idx_rv_id_users');

            // WHERE LOWER(estado) IN (...) — terminar viaje, asignar viaje
            $table->index('estado', 'idx_rv_estado');

            // WHERE id_carros=? AND viaje_numero=? AND asiento=? — check de asiento (race condition lock)
            $table->index(['id_carros', 'viaje_numero', 'asiento'], 'idx_rv_carros_viaje_asiento');
        });

        // ── carros ───────────────────────────────────────────────────────────
        Schema::table('carros', function (Blueprint $table) {
            // WHERE id_users = ? — mis-carros, historial-conductor, ownership
            $table->index('id_users', 'idx_carros_id_users');

            // WHERE id_estados NOT IN (4,5) — GetAll paginado (lista pública)
            $table->index('id_estados', 'idx_carros_id_estados');
        });

        // ── Factura ──────────────────────────────────────────────────────────
        Schema::table('Factura', function (Blueprint $table) {
            // WHERE id_users = ? — mis facturas del usuario
            $table->index('id_users', 'idx_factura_id_users');
        });

        // ── users ────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // WHERE name = ? — lookup conductor por nombre en notificaciones
            $table->index('name', 'idx_users_name');
        });
    }

    public function down(): void
    {
        Schema::table('reservarviajes', function (Blueprint $table) {
            $table->dropIndex('idx_rv_id_carros');
            $table->dropIndex('idx_rv_id_users');
            $table->dropIndex('idx_rv_estado');
            $table->dropIndex('idx_rv_carros_viaje_asiento');
        });

        Schema::table('carros', function (Blueprint $table) {
            $table->dropIndex('idx_carros_id_users');
            $table->dropIndex('idx_carros_id_estados');
        });

        Schema::table('Factura', function (Blueprint $table) {
            $table->dropIndex('idx_factura_id_users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_name');
        });
    }
};
