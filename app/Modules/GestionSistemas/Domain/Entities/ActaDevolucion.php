<?php

namespace App\Modules\GestionSistemas\Domain\Entities;

class ActaDevolucion
{
    private ?int $id;
    private int $entregaId;
    private string $fechaDevolucion;
    private ?string $firmaEntrega;
    private ?string $firmaRecibe;
    private ?string $observaciones;

    public function __construct(
        int $entregaId,
        string $fechaDevolucion,
        ?string $observaciones = null,
        ?string $firmaEntrega = null,
        ?string $firmaRecibe = null,
        ?int $id = null
    ) {
        $this->entregaId = $entregaId;
        $this->fechaDevolucion = $fechaDevolucion;
        $this->observaciones = $observaciones;
        $this->firmaEntrega = $firmaEntrega;
        $this->firmaRecibe = $firmaRecibe;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getEntregaId(): int { return $this->entregaId; }
    public function getFechaDevolucion(): string { return $this->fechaDevolucion; }
    public function getFirmaEntrega(): ?string { return $this->firmaEntrega; }
    public function getFirmaRecibe(): ?string { return $this->firmaRecibe; }
    public function getObservaciones(): ?string { return $this->observaciones; }

    public function setFirmaEntrega(?string $firma): void { $this->firmaEntrega = $firma; }
    public function setFirmaRecibe(?string $firma): void { $this->firmaRecibe = $firma; }
}
