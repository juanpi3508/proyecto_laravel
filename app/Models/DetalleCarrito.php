<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCarrito extends Model
{
    protected $table = 'detalle_carrito';

    public $timestamps = false;

    protected $fillable = [
        'id_carrito',
        'id_producto',
        'cantidad',
        'precio_unitario'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'float'
    ];
    public function producto()
    {
        return $this->belongsTo(
            Product::class,
            'id_producto',
            'id_producto'
        );
    }

    public function carrito()
    {
        return $this->belongsTo(
            Carrito::class,
            'id_carrito',
            'codigo'
        );
    }

    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function incrementarCantidad(int $cantidad = 1): void
    {
        $this->cantidad += $cantidad;
    }
}
