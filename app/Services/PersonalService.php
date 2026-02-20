<?php

namespace App\Services;

use App\Models\Personal;
use Illuminate\Support\Facades\Log;

class PersonalService
{
    public function __construct(
        protected KubappService $kubappService
    ) {}

    /**
     * Busca personal en BD local. Si no hay resultados y hay término de búsqueda,
     * hace fallback a Kubapp para buscar y auto-registrar.
     */
    public function getAll($search = null)
    {
        $query = Personal::with('cargo');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cedula', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        $localResults = $query->get();

        // Si hay resultados locales o no hay búsqueda, retornar lo local
        if ($localResults->isNotEmpty() || !$search) {
            return $localResults;
        }

        // Fallback a Kubapp — solo si hay un término de búsqueda y no se encontró nada local
        return $this->searchKubappAndSync($search);
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
                        'cargo_id' => null,
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
