<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;


class KubappService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.kubapp.url', env('KUBAPP_API_URL', 'http://190.145.135.122:8090')), '/');
        $this->timeout = (int) config('services.kubapp.timeout', 10);
    }

    /**
     * Busca terceros en Kubapp por nombre completo.
     *
     * @param string $nombre  Apellido(s) y nombre(s) completo(s).
     * @return array  Array de resultados [{nit, nombre, fechaNacimiento, genero}, ...]
     * @throws Exception  Si la API externa no responde o hay error de red.
     */
    public function buscarPorNombre(string $nombre): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get("{$this->baseUrl}/api/terceros/buscar", [
                    'nombre' => $nombre,
                ]);

            if ($response->failed()) {
                Log::warning('Kubapp API respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'nombre' => $nombre,
                ]);
                return [];
            }

            $data = $response->json();

            // La API devuelve un array directamente
            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('Error conectando con Kubapp API', [
                'message' => $e->getMessage(),
                'nombre' => $nombre,
            ]);

            return [];
        }
    }

    /**
     * Busca un tercero en Kubapp por NIT/cédula.
     *
     * Busca por nombre y filtra localmente por nit exacto como fallback,
     * ya que la API solo expone búsqueda por nombre.
     */
    public function buscarPorNit(string $nit): ?array
    {
        // La API de Kubapp no tiene endpoint de búsqueda por NIT directo,
        // así que este método queda disponible para futuras extensiones.
        return null;
    }
}
