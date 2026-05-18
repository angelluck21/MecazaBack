<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Factura', function (Blueprint $table) {
            $table->string('origen')->nullable()->after('destino');
        });
    }

    public function down(): void
    {
        Schema::table('Factura', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
