<?php

namespace App\Modules\GestionSistemas\Domain\Entities;

class ActaEntrega
{
    private ?int $id;
    private int $equipoId;
    private int $funcionarioId;
    private string $fechaEntrega;
    private ?string $firmaEntrega;
    private ?string $firmaRecibe;
    private ?string $devuelto;
    private string $estado;
    /** @var PerifericoEntregado[] */
    private array $perifericos;

    public function __construct(
        int $equipoId,
        int $funcionarioId,
        string $fechaEntrega,
        ?string $firmaEntrega = null,
        ?string $firmaRecibe = null,
        string $estado = 'entregado',
        ?string $devuelto = null,
        array $perifericos = [],
        ?int $id = null
    ) {
        $this->equipoId = $equipoId;
        $this->funcionarioId = $funcionarioId;
        $this->fechaEntrega = $fechaEntrega;
        $this->firmaEntrega = $firmaEntrega;
        $this->firmaRecibe = $firmaRecibe;
        $this->estado = $estado;
        $this->devuelto = $devuelto;
        $this->perifericos = $perifericos;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipoId(): int
    {
        return $this->equipoId;
    }

    public function getFuncionarioId(): int
    {
        return $this->funcionarioId;
    }

    public function getFechaEntrega(): string
    {
        return $this->fechaEntrega;
    }

    public function getFirmaEntrega(): ?string
    {
        return $this->firmaEntrega;
    }

    public function getFirmaRecibe(): ?string
    {
        return $this->firmaRecibe;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getDevuelto(): ?string
    {
        return $this->devuelto;
    }

    /**
     * @return PerifericoEntregado[]
     */
    public function getPerifericos(): array
    {
        return $this->perifericos;
    }

    public function setFirmaEntrega(string $firma): void
    {
        $this->firmaEntrega = $firma;
    }

    public function setFirmaRecibe(string $firma): void
    {
        $this->firmaRecibe = $firma;
    }
}
