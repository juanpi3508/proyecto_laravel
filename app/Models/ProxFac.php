<?php

namespace App\Models;

use App\Constants\ProxFacColumns as Col;
use Illuminate\Database\Eloquent\Model;

class ProxFac extends Model
{
    protected $table = Col::TABLE;

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

        $precioUnitario = $producto->precioVenta();

        return self::create([
            Col::FACTURA  => $idFactura,
            Col::PRODUCTO => (string) $producto->getKey(),
            Col::CANTIDAD => $cantidad,
            Col::PRECIO   => $precioUnitario,
            Col::SUBTOTAL => $precioUnitario * $cantidad,
            Col::ESTADO   => config('facturas.estados.abierta'),
        ]);
    }
}
