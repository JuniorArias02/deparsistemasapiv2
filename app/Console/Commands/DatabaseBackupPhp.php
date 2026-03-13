<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackupPhp extends Command
{
    /**
     * El nombre y firma del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'db:backup-php';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera un backup de la base de datos usando PHP puro (sin mysqldump), compatible con Hostinger.';

    /**
     * Tablas a ignorar en el backup.
     */
    protected $ignoredTables = [
        'sessions',
        'jobs',
        'failed_jobs',
        'cache',
        'cache_locks'
    ];

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('--- NexaCore PHP Backup System (Production Level) ---');

        $database = config('database.connections.mysql.database');
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$date}.sql.gz";
        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $fullPath = "{$backupPath}/{$filename}";

        try {
            $this->info("Iniciando exportación de la base de datos: {$database}");
            
            // Abrir un stream para escribir el archivo directamente (mejor para memoria)
            // Sin embargo, para gzencode() lo haremos en bloques o al final si no es gigante.
            // Para ser PRO, usaremos gzopen para escribir directamente comprimido.
            $zp = gzopen($fullPath, "w9");

            if (!$zp) {
                throw new \Exception("No se pudo crear el archivo de backup en {$fullPath}");
            }

            // Encabezado del archivo SQL
            gzwrite($zp, "-- NexaCore Database Backup\n");
            gzwrite($zp, "-- Fecha: " . Carbon::now()->toDateTimeString() . "\n");
            gzwrite($zp, "-- Database: {$database}\n\n");
            gzwrite($zp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_{$database}";

            foreach ($tables as $tableItem) {
                $tableName = $tableItem->$tableKey;

                if (in_array($tableName, $this->ignoredTables)) {
                    $this->line("Saltando tabla ignorada: {$tableName}");
                    continue;
                }

                $this->info("Procesando tabla: {$tableName}...");

                // Estructura de la tabla
                gzwrite($zp, "\n-- Estructura de la tabla `{$tableName}`\n");
                gzwrite($zp, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createSql = $createTable->{'Create Table'} . ";\n\n";
                gzwrite($zp, $createSql);

                // Datos de la tabla usando Chunk para no reventar la memoria
                gzwrite($zp, "-- Datos de la tabla `{$tableName}`\n");
                
                // Necesitamos una columna para ordenar en chunk
                // Intentamos buscar la PK, si no, usamos el primer campo.
                $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
                $primaryKey = $columns[0]->Field; // Fallback
                foreach ($columns as $column) {
                    if ($column->Key === 'PRI') {
                        $primaryKey = $column->Field;
                        break;
                    }
                }

                DB::table($tableName)->orderBy($primaryKey)->chunk(500, function ($rows) use ($zp, $tableName) {
                    if ($rows->count() > 0) {
                        foreach ($rows as $row) {
                            $rowData = (array)$row;
                            $values = array_map(function ($value) {
                                if ($value === null) return 'NULL';
                                // Escapar caracteres especiales para SQL
                                return "'" . addslashes($value) . "'";
                            }, $rowData);

                            $sql = "INSERT INTO `{$tableName}` VALUES (" . implode(", ", $values) . ");\n";
                            gzwrite($zp, $sql);
                        }
                    }
                });

                gzwrite($zp, "\n");
            }

            gzwrite($zp, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($zp);

            // Verificar integridad
            if (File::size($fullPath) > 0) {
                $this->info("¡Éxito! Backup guardado y comprimido en: storage/app/backups/{$filename}");
                Log::info("Backup PHP exitoso: {$filename}");
                
                // Limpiar backups antiguos (más de 15 días)
                $this->cleanupOldBackups($backupPath);
            } else {
                File::delete($fullPath);
                throw new \Exception("El archivo generado está vacío.");
            }

        } catch (\Exception $e) {
            $this->error("Error crítico en el backup: " . $e->getMessage());
            Log::error("Error en Backup PHP: " . $e->getMessage());
            if (isset($fullPath) && File::exists($fullPath)) {
                File::delete($fullPath);
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Elimina archivos antiguos (más de 15 días).
     */
    protected function cleanupOldBackups($path)
    {
        $this->info("Revisando backups antiguos para limpieza...");
        $files = File::files($path);
        $now = time();
        $days = 15;

        foreach ($files as $file) {
            if ($now - $file->getMTime() >= $days * 24 * 60 * 60) {
                File::delete($file->getPathname());
                $this->line("Eliminado backup antiguo: " . $file->getFilename());
            }
        }
    }
}
