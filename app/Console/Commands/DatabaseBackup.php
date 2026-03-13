<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * El nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera un backup de la base de datos MySQL de forma organizada.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('--- NexaCore Backup System ---');
        
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$date}.sql";
        $backupPath = storage_path('app/backups');

        // Crear directorio si no existe
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump');
        $fullPath = "{$backupPath}/{$filename}";

        $this->info("Generando copia de seguridad: {$filename}...");

        // Comando mysqldump
        $command = sprintf(
            '%s --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($fullPath)
        );

        $output = [];
        $resultCode = null;

        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("¡Éxito! Backup guardado en: storage/app/backups/{$filename}");
            Log::info("Backup de base de datos exitoso: {$filename}");
            
            // Política de Retención: Eliminar backups de más de 15 días
            $this->cleanupOldBackups($backupPath);
        } else {
            $this->error("Error al generar el backup. Código de salida: {$resultCode}");
            Log::error("Error crítico: No se pudo generar el backup de la base de datos.");
            $this->warn("Nota: Asegúrese de que 'mysqldump' esté disponible en las variables de entorno (PATH).");
        }
    }

    /**
     * Elimina archivos antiguos para ahorrar espacio en disco.
     */
    protected function cleanupOldBackups($path)
    {
        $this->info("Limpiando backups antiguos (mayores a 15 días)...");
        
        $files = glob("{$path}/*.sql");
        $now = time();
        $retentionDays = 15;

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= $retentionDays * 24 * 60 * 60) {
                    unlink($file);
                    $this->line("Eliminado: " . basename($file));
                }
            }
        }
    }
}
