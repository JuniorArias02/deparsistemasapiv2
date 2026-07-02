<?php

namespace App\Modules\GestionCompras\Application\DTOs;

class ProgramarPedidoDTO
{
    public function __construct(
        public readonly array $datosPedido,
        public readonly string $fechaProgramada,
        public readonly int $creadoPor,
        public readonly ?string $firmaBase64 = null,
        public readonly ?object $firmaFile = null,
        public readonly bool $useStoredSignature = false
    ) {}
}
