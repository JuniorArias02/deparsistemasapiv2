<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseRestore extends Command
{
    /**
     * El nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'db:restore {file? : El nombre del archivo de backup a restaurar}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Restaura la base de datos MySQL desde un archivo de backup.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('--- NexaCore Database Restore System ---');
        
        $backupDirectory = storage_path('app/backups');

        if (!File::exists($backupDirectory)) {
            $this->error("El directorio de backups no existe: {$backupDirectory}");
            return Command::FAILURE;
        }

        $files = File::files($backupDirectory);
        $sqlFiles = array_filter($files, fn($file) => $file->getExtension() === 'sql');

        if (empty($sqlFiles)) {
            $this->error("No se encontraron archivos de backup (.sql) en: {$backupDirectory}");
            return Command::FAILURE;
        }

        // Sort files by modification time descending (newest first)
        usort($sqlFiles, fn($a, $b) => $b->getMTime() - $a->getMTime());

        $fileChoices = array_map(fn($file) => $file->getFilename(), $sqlFiles);
        
        $selectedFile = $this->argument('file');

        if (!$selectedFile) {
            $selectedFile = $this->choice(
                'Seleccione el archivo de backup que desea restaurar:',
                $fileChoices,
                0 // Default to the most recent one
            );
        }

        $filePath = "{$backupDirectory}/{$selectedFile}";

        if (!File::exists($filePath)) {
            $this->error("El archivo seleccionado no existe: {$filePath}");
            return Command::FAILURE;
        }

        $this->warn("!!! ADVERTENCIA: Esta acción SOBRESCRIBIRÁ completamente la base de datos actual !!!");
        if (!$this->confirm("¿Está seguro de que desea restaurar el backup: {$selectedFile}?", false)) {
            $this->info('Operación cancelada.');
            return Command::SUCCESS;
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $mysqlPath = env('MYSQL_PATH', 'mysql');

        $this->info("Restaurando base de datos: {$database}...");

        // Build mysql command
        $command = sprintf(
            '%s --user=%s --password=%s --host=%s %s < %s',
            escapeshellarg($mysqlPath),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        $output = [];
        $resultCode = null;

        $this->warn("Procesando... por favor espere.");
        
        \exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("¡Éxito! La base de datos ha sido restaurada correctamente desde {$selectedFile}");
            Log::info("Restauración de base de datos exitosa: {$selectedFile}");
            return Command::SUCCESS;
        } else {
            $this->error("Error al restaurar la base de datos. Código de salida: {$resultCode}");
            Log::error("Error crítico: No se pudo restaurar la base de datos desde {$selectedFile}.");
            return Command::FAILURE;
        }
    }
}
