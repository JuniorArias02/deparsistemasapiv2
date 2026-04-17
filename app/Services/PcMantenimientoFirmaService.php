<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PcMantenimientoFirmaService
{
    /**
     * Guarda una firma en base64 en el almacenamiento.
     *
     * @param string|null $base64String
     * @return string|null El path relativo de la imagen guardada o null.
     */
    public function saveBase64Signature(?string $base64String)
    {
        if (!$base64String || !str_contains($base64String, ';base64,')) {
            return null;
        }

        try {
            // Extraer la extensión y el contenido base64
            // format: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...
            @list($type, $data) = explode(';', $base64String);
            @list(, $data) = explode(',', $data);
            
            if (!$data) return null;

            $image = base64_decode($data);
            $extension = 'png'; // SignaturePad usually sends png
            
            if (str_contains($type, '/')) {
                $extension = explode('/', $type)[1];
            }
            
            $fileName = Str::random(40) . '.' . $extension;
            $path = 'firmaMantenimientoPc/' . $fileName;
            
            Storage::disk('public')->put($path, $image);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Error al guardar firma base64: ' . $e->getMessage());
            return null;
        }
    }
}
