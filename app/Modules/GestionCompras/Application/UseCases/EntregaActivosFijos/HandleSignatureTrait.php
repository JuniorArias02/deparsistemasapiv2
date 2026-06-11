<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use Illuminate\Support\Facades\Storage;
use Exception;

trait HandleSignatureTrait
{
    protected function handleSignature($file, $useStored, $user, $prefix)
    {
        $path = null;

        if ($useStored && $user) {
            $originalPath = $user->getAttributes()['firma_digital'] ?? null;

            if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                throw new Exception('No se encontró una firma digital guardada en su perfil.');
            }

            $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
            $filename = $prefix . '_' . time() . '_stored.' . $extension;
            $newPath = 'entrega_activos_firma/' . $filename;

            Storage::disk('public')->copy($originalPath, $newPath);
            $path = $newPath;
        } elseif ($file) {
            $filename = $prefix . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('entrega_activos_firma', $filename, 'public');
        }

        return $path;
    }
}