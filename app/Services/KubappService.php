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
        $principalIp = '190.145.135.122';
        $clientIp = request()->ip();

        /*
        if ($clientIp === $principalIp) {
            $this->baseUrl = rtrim(env('KUBAPP_API_URL_PRINCIPAL', 'http://192.168.0.13:8090/api'), '/');
        } else {
            $this->baseUrl = rtrim(env('KUBAPP_API_URL_DEMAS_SEDES', 'http://190.145.135.122:8090/api'), '/');
        }
        */
        $this->baseUrl = rtrim(env('KUBAPP_API_URL_DEMAS_SEDES', 'http://190.145.135.122:8090/api'), '/');

        $this->timeout = (int) config('services.kubapp.timeout', 30);
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
        $url = "{$this->baseUrl}/terceros/buscar?nombre=" . urlencode($nombre);
        
        Log::info('Llamando a Kubapp API', [
            'url' => $url,
            'client_ip' => request()->ip()
        ]);

        try {
            // Un GET puro sin body ni parámetros extras en la función get()
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url);

            Log::info('Respuesta de Kubapp API recibida', [
                'status' => $response->status(),
                'url_final' => $response->effectiveUri()->__toString()
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

            // La API puede devolver los resultados dentro de una clave 'content' (paginado)
            // o como un array directo.
            if (isset($data['content']) && is_array($data['content'])) {
                return $data['content'];
            }

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
        return null;
    }

    /**
     * Busca artículos (productos/servicios) en Kubapp por término.
     */
    public function buscarArticulo(string $termino): array
    {
        $url = "{$this->baseUrl}/articulos/buscar?termino=" . urlencode($termino);

        Log::info('Llamando a Kubapp API para artículos', [
            'url' => $url,
            'termino' => $termino
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url);

            if ($response->failed()) {
                Log::warning('Kubapp API Artículos respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            $results = is_array($data) ? $data : [];

            // Guardar automáticamente en la base de datos local
            foreach ($results as $item) {
                \App\Models\CpProductoServicio::updateOrCreate(
                    ['codigo_producto' => $item['codigoProd']],
                    ['nombre' => $item['nombreProd']]
                );
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Error conectando con Kubapp API Artículos', [
                'message' => $e->getMessage(),
                'termino' => $termino,
            ]);
            return [];
        }
    }
}
