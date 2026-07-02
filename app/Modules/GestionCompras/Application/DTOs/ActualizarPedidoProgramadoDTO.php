<?php

namespace App\Modules\GestionCompras\Application\DTOs;

class ActualizarPedidoProgramadoDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?array $datosPedido = null,
        public readonly ?string $fechaProgramada = null,
        public readonly ?string $firmaBase64 = null,
        public readonly ?object $firmaFile = null,
        public readonly bool $useStoredSignature = false
    ) {}
}
