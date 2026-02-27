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
        Schema::table('cp_pedidos', function (Blueprint $table) {
            $table->text('motivo_aprobacion_compras')->nullable()->after('responsable_aprobacion_firma');
            $table->text('motivo_rechazado_compras')->nullable()->after('estado_gerencia');
            $table->text('motivo_aprobacion_gerencia')->nullable()->after('fecha_gerencia');
            $table->text('motivo_rechazado_gerencia')->nullable()->after('motivo_aprobacion_gerencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cp_pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'motivo_aprobacion_compras',
                'motivo_rechazado_compras',
                'motivo_aprobacion_gerencia',
                'motivo_rechazado_gerencia'
            ]);
        });
    }
};