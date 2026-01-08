<?php

namespace App\Models;

use App\Constants\ProxFacColumns as Col;
use Illuminate\Database\Eloquent\Model;

class ProxFac extends Model
{
    protected $table = 'proxfac';

    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        Col::FACTURA,
        Col::PRODUCTO,
        Col::CANTIDAD,
        Col::PRECIO,
        Col::SUBTOTAL,
        Col::ESTADO,
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, Col::FACTURA);
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, Col::PRODUCTO);
    }

    public static function crearDesdeProducto(
        string $idFactura,
        Product $producto,
        int $cantidad
    ): self {
        if ($cantidad <= 0) {
            throw new \Exception(
                config('facturas.mensajes.cantidad_invalida_detalle')
            );
        }

        return self::create([
            Col::FACTURA  => $idFactura,
            Col::PRODUCTO => $producto->id_producto,
            Col::CANTIDAD => $cantidad,
            Col::PRECIO   => $producto->pro_precio_venta,
            Col::SUBTOTAL => $producto->pro_precio_venta * $cantidad,
            Col::ESTADO   => config('facturas.estados.abierta'),
        ]);
    }
}
