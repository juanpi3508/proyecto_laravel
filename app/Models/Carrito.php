<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Carrito extends Model
{
    protected $table = 'carritos';

    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'fecha',
        'iva',
        'tipo'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'iva'   => 'float'
    ];

    public function detalles()
    {
        return $this->hasMany(
            DetalleCarrito::class,
            'id_carrito',
            'codigo'
        );
    }

    public function agregarProducto(Product $producto, int $cantidad = 1): void
    {
        $detalle = $this->detalles()
            ->where('id_producto', $producto->id_producto)
            ->first();

        if ($detalle) {
            $detalle->incrementarCantidad($cantidad);
            $detalle->save();
        } else {
            $this->detalles()->create([
                'id_producto'    => $producto->id_producto,
                'cantidad'       => $cantidad,
                'precio_unitario'=> $producto->pro_precio_venta
            ]);
        }
    }

    public function eliminarProducto(string $idProducto): void
    {
        $this->detalles()
            ->where('id_producto', $idProducto)
            ->delete();
    }

    public function vaciarCarrito(): void
    {
        $this->detalles()->delete();
    }

    public function estaVacio(): bool
    {
        return $this->detalles()->count() === 0;
    }

    public function getSubtotalAttribute(): float
    {
        return $this->detalles->sum(fn ($d) => $d->subtotal);
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + ($this->subtotal * $this->iva);
    }
}
