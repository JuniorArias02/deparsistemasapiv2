<?php

namespace App\Services;

use App\Models\Personal;
use Illuminate\Support\Facades\Log;

class PersonalService
{
    public function __construct(
        protected KubappService $kubappService
    ) {}

    public function getAll($search = null, $externalSearch = false, $estado = null)
    {
        $query = Personal::with('cargo');

        if ($estado !== null) {
            $query->where('estado', $estado);
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $searchTerms = explode(' ', $search);
                foreach ($searchTerms as $term) {
                    if (empty($term)) continue;
                    $q->where(function($sq) use ($term) {
                        $sq->where('cedula', 'like', "%{$term}%")
                          ->orWhere('nombre', 'like', "%{$term}%");
                    });
                }
            });
        }

        $localResults = $query->get();

        // Si hay resultados locales o no hay búsqueda, retornar lo local
        if ($localResults->isNotEmpty() || !$search) {
            return $localResults;
        }

        // Solo buscar en Kubapp si se solicita explícitamente la búsqueda externa
        if ($externalSearch) {
            return $this->searchKubappAndSync($search);
        }

        return collect(); // Retornar vacío si no hay resultados locales y no se pidió búsqueda externa
    }

    /**
     * Busca en Kubapp y sincroniza resultados a la BD local.
     * Previene duplicados usando firstOrCreate con la cédula/nit.
     */
    private function searchKubappAndSync(string $search)
    {
        $kubappResults = $this->kubappService->buscarPorNombre($search);

        if (empty($kubappResults)) {
            return collect(); 
        }

        $synced = collect();

        foreach ($kubappResults as $tercero) {
            if (empty($tercero['nit']) || empty($tercero['nombre'])) {
                continue;
            }

            try {
                $personal = Personal::firstOrCreate(
                    ['cedula' => $tercero['nit']],
                    [
                        'nombre' => $tercero['nombre'],
                        'telefono' => null,
                        'cargo_id' => null, // Dejamos el cargo en nulo
                    ]
                );

                $personal->load('cargo');
                $synced->push($personal);
            } catch (\Exception $e) {
                Log::warning('Error sincronizando personal desde Kubapp', [
                    'nit' => $tercero['nit'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $synced;
    }

    public function create(array $data)
    {
        return Personal::create($data);
    }

    public function find($id)
    {
        return Personal::with('cargo')->find($id);
    }

    public function update($id, array $data)
    {
        $personal = Personal::find($id);
        if ($personal) {
            $personal->update($data);
        }
        return $personal;
    }

    public function delete($id)
    {
        $personal = Personal::find($id);
        if ($personal) {
            $personal->delete();
            return true;
        }
        return false;
    }
}
