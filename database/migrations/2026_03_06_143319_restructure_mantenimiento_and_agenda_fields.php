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
        // 1. Update mantenimientos table
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign('mantenimientos_ibfk_2');

            // Rename column
            $table->renameColumn('nombre_receptor', 'coordinador_id');

            // Add foreign key back
            $table->foreign('coordinador_id', 'mantenimientos_coordinador_id_foreign')
                ->references('id')
                ->on('usuarios');
        });

        // 2. Update agenda_mantenimientos table
        Schema::table('agenda_mantenimientos', function (Blueprint $table) {
            $table->renameColumn('creado_por', 'tecnico_id');
            $table->renameColumn('agendado_por', 'coordinador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_mantenimientos', function (Blueprint $table) {
            $table->renameColumn('tecnico_id', 'creado_por');
            $table->renameColumn('coordinador_id', 'agendado_por');
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeign('mantenimientos_coordinador_id_foreign');
            $table->renameColumn('coordinador_id', 'nombre_receptor');
            $table->foreign('nombre_receptor', 'mantenimientos_ibfk_2')
                ->references('id')
                ->on('usuarios');
        });
    }
};
