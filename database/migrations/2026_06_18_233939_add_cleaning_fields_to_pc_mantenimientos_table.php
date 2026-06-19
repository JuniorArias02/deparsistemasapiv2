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
        Schema::table('pc_mantenimientos', function (Blueprint $table) {
            $table->boolean('cpu')->default(false)->after('estado');
            $table->boolean('pantalla')->default(false)->after('cpu');
            $table->boolean('teclado')->default(false)->after('pantalla');
            $table->boolean('mouse')->default(false)->after('teclado');
            $table->boolean('unidad_cd')->default(false)->after('mouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pc_mantenimientos', function (Blueprint $table) {
            $table->dropColumn(['cpu', 'pantalla', 'teclado', 'mouse', 'unidad_cd']);
        });
    }
};
