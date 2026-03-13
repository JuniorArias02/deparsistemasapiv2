<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseRestorePhp extends Command
{
    /**
     * El nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'db:restore-php {file? : El nombre del archivo .sql.gz a restaurar}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Restaura la base de datos desde un archivo .sql.gz generado por db:backup-php.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('--- NexaCore PHP Restore System (Production Level) ---');

        $backupDirectory = storage_path('app/backups');

        if (!File::exists($backupDirectory)) {
            $this->error("No se encontró el directorio de backups.");
            return Command::FAILURE;
        }

        $files = File::files($backupDirectory);
        $backupFiles = array_filter($files, fn($file) => $file->getExtension() === 'gz');

        if (empty($backupFiles)) {
            $this->error("No se encontraron archivos de backup (.sql.gz)");
            return Command::FAILURE;
        }

        // Ordenar por fecha (más reciente primero)
        usort($backupFiles, fn($a, $b) => $b->getMTime() - $a->getMTime());
        $choices = array_map(fn($file) => $file->getFilename(), $backupFiles);

        $selectedFile = $this->argument('file');

        if (!$selectedFile) {
            $selectedFile = $this->choice(
                'Seleccione el backup para restaurar:',
                $choices,
                0
            );
        }

        $filePath = "{$backupDirectory}/{$selectedFile}";

        if (!File::exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return Command::FAILURE;
        }

        $this->warn("!!! ADVERTENCIA: Esta acción eliminará los datos actuales y restaurará los del backup !!!");
        if (!$this->confirm("¿Está seguro de restaurar: {$selectedFile}?", false)) {
            $this->info("Operación cancelada.");
            return Command::SUCCESS;
        }

        try {
            $this->info("Iniciando restauración... esto puede tardar unos minutos.");
            
            // Usar una transacción para seguridad total
            DB::beginTransaction();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $zp = gzopen($filePath, "rb");
            if (!$zp) throw new \Exception("No se pudo abrir el archivo comprimido.");

            $sqlBatch = "";
            while (!gzeof($zp)) {
                $line = gzgets($zp, 4096);
                
                // Ignorar comentarios y líneas vacías
                if (trim($line) == '' || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                    continue;
                }

                $sqlBatch .= $line;

                // Si la línea termina en punto y coma, ejecutamos el lote acumulado
                if (substr(trim($line), -1) == ';') {
                    DB::unprepared($sqlBatch);
                    $sqlBatch = "";
                }
            }
            
            gzclose($zp);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            $this->info("¡Éxito! La base de datos ha sido restaurada correctamente.");
            Log::info("Restauración PHP exitosa: {$selectedFile}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error crítico durante la restauración: " . $e->getMessage());
            Log::error("Error en Restore PHP: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
