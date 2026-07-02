<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\MicroserviceConnectionAlert;

class CheckMicroserviceConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:microservice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la conexión con el microservicio convertidor y envía alertas si falla.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('services.convertidor.url');
        $alertEmailRaw = config('services.convertidor.alert_email');
        
        if (!$url || !$alertEmailRaw) {
            $this->warn('URL o Email de alerta no están configurados.');
            return;
        }

        $alertEmails = array_filter(array_map('trim', explode(',', $alertEmailRaw)));

        if (empty($alertEmails)) {
            $this->warn('No hay correos electrónicos válidos configurados para alertas.');
            return;
        }

        $this->info("Haciendo ping a: {$url}");

        // El estado actual en caché. True significa que está actualmente fallando (caído).
        $isCurrentlyDown = Cache::get('microservice_convertidor_is_down', false);
        
        try {
            // Hacemos una petición GET simple con timeout corto para no colgar el servidor
            $response = Http::timeout(5)->get($url);

            if ($response->successful() || $response->status() !== 0) {
                // Si responde algo (incluso un 404), significa que el servidor está vivo y hay conexión
                if ($isCurrentlyDown) {
                    $this->info('El servicio se ha recuperado. Enviando correo de recuperación...');
                    $this->sendAlertEmail('up', $url, $alertEmails);
                    Cache::put('microservice_convertidor_is_down', false);
                } else {
                    $this->info('El servicio está funcionando correctamente.');
                }
            } else {
                throw new \Exception("Status Code: " . $response->status());
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->error("Fallo de conexión: {$errorMessage}");

            if (!$isCurrentlyDown) {
                $this->info('Detectada nueva caída. Enviando correo de alerta...');
                $this->sendAlertEmail('down', $url, $alertEmails, $errorMessage);
                // Lo marcamos como caído indefinidamente hasta que responda un status de nuevo
                Cache::put('microservice_convertidor_is_down', true);
            } else {
                $this->info('El servicio sigue caído. No se envía nuevo correo para evitar spam.');
            }
        }
    }

    private function sendAlertEmail(string $status, string $url, array $emails, string $errorMessage = '')
    {
        try {
            Mail::to($emails)->send(new MicroserviceConnectionAlert($status, $url, $errorMessage));
        } catch (\Exception $e) {
            $this->error("No se pudo enviar el correo de alerta: " . $e->getMessage());
        }
    }
}
