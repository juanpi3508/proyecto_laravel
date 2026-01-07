<?php

namespace App\Models;

class DetalleCarrito
{
    public string $id_producto;
    public int $cantidad;
    public float $precio_unitario;
    public int $stock;
    public ?string $descripcion;
    public ?string $imagen;

    public function __construct(
        string $id_producto,
        int $cantidad,
        float $precio_unitario,
        int $stock,
        ?string $descripcion = null,
        ?string $imagen = null
    ) {
        $this->id_producto = $id_producto;
        $this->cantidad = max(1, $cantidad);
        $this->precio_unitario = $precio_unitario;
        $this->stock = max(0, $stock);
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;

        $this->normalizarCantidad();
    }


    public function incrementarCantidad(int $cantidad = 1): void
    {
        $this->cantidad += $cantidad;
        $this->normalizarCantidad();
    }

    public function actualizarCantidad(int $cantidad): void
    {
        $this->cantidad = $cantidad;
        $this->normalizarCantidad();
    }

    public function subtotal(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }
    private function normalizarCantidad(): void
    {
        if ($this->cantidad < 1) {
            $this->cantidad = 1;
        }

        if ($this->cantidad > $this->stock) {
            $this->cantidad = $this->stock;
        }
    }

    public function __get(string $name)
    {
        return match ($name) {
            'precio'   => $this->precio_unitario,
            'subtotal' => $this->subtotal(),
            default    => null,
        };
    }

}
