<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\SugerenciaAdjunto;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Domain\Events\NuevaEvidenciaAdjunta;
use Illuminate\Http\UploadedFile;

class CargarEvidenciaVisualUseCase
{
    public function execute(int $sugerenciaId, array $archivos)
    {
        $adjuntos = [];
        
        foreach ($archivos as $archivo) {
            if ($archivo instanceof UploadedFile) {
                $path = $archivo->store('buzon_imagenes', 'public');
                $adjunto = SugerenciaAdjunto::create([
                    'sugerencia_id' => $sugerenciaId,
                    'url_imagen' => 'storage/' . $path,
                    'fecha_subida' => now(),
                ]);
                $adjuntos[] = $adjunto;
            }
        }
        
        if (count($adjuntos) > 0) {
            $sugerencia = BuzonSugerencia::find($sugerenciaId);
            if ($sugerencia) {
                event(new NuevaEvidenciaAdjunta($adjuntos, $sugerencia->codigo_ticket));
            }
        }
        
        return $adjuntos;
    }
}
