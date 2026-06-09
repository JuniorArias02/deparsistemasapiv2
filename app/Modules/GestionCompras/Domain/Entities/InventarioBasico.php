<?php

namespace App\Modules\GestionCompras\Domain\Entities;

class InventarioBasico
{
    private int $id;
    private string $codigo;
    private string $nombre;
    private ?string $marca;
    private ?string $modelo;
    private ?string $serial;

    public function __construct(
        int $id,
        string $codigo,
        string $nombre,
        ?string $marca = null,
        ?string $modelo = null,
        ?string $serial = null
    ) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->serial = $serial;
    }

    public function getId(): int { return $this->id; }
    public function getCodigo(): string { return $this->codigo; }
    public function getNombre(): string { return $this->nombre; }
    public function getMarca(): ?string { return $this->marca; }
    public function getModelo(): ?string { return $this->modelo; }
    public function getSerial(): ?string { return $this->serial; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serial' => $this->serial
        ];
    }
}
