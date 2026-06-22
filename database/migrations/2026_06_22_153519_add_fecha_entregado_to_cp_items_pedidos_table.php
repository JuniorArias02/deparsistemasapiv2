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
        Schema::table('cp_items_pedidos', function (Blueprint $table) {
            $table->dateTime('fecha_entregado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cp_items_pedidos', function (Blueprint $table) {
            $table->dropColumn('fecha_entregado');
        });
    }
};
