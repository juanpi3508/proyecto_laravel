<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Carrito
{
    protected Collection $items;

    protected float $iva;

    public function __construct(
        Collection $items = null,
        ?float $iva = null
    ) {
        $this->items = $items ?? collect();
        $this->iva   = $iva ?? config('carrito.iva');
    }

    public function agregarProducto(DetalleCarrito $detalle): void
    {
        $existente = $this->items->firstWhere(
            'id_producto',
            $detalle->id_producto
        );

        if ($existente) {
            $existente->incrementarCantidad($detalle->cantidad);
        } else {
            $this->items->push($detalle);
        }
    }

    public function eliminarProducto(string $idProducto): void
    {
        $this->items = $this->items
            ->reject(fn ($item) => $item->id_producto === $idProducto)
            ->values();
    }

    public function vaciarCarrito(): void
    {
        $this->items = collect();
    }

    public function estaVacio(): bool
    {
        return $this->items->isEmpty();
    }

    public function subtotal(): float
    {
        return $this->items->sum(
            fn (DetalleCarrito $d) => $d->subtotal()
        );
    }

    public function impuestos(): float
    {
        return $this->subtotal() * $this->iva;
    }

    public function total(): float
    {
        return $this->subtotal() + $this->impuestos();
    }

    public function totalArticulos(): int
    {
        return $this->items->sum(
            fn (DetalleCarrito $d) => $d->cantidad
        );
    }

    public function items(): Collection
    {
        return $this->items;
    }

    public function iva(): float
    {
        return $this->iva;
    }
}
