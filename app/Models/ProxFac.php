<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxFac extends Model
{
    protected $table = 'proxfac';

    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'id_factura',
        'id_producto',
        'pxf_cantidad',
        'pxf_precio_venta',
        'pxf_subtotal_producto',
        'estado_pxf'
    ];

    /* ===================== RELACIONES ===================== */

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'id_producto');
    }

    /* ===================== LÓGICA DE NEGOCIO ===================== */

    public static function crearDesdeProducto(
        string $idFactura,
        Product $producto,
        int $cantidad
    ): self {
        if ($cantidad <= 0) {
            throw new \Exception('Cantidad inválida en detalle de factura.');
        }

        return self::create([
            'id_factura' => $idFactura,
            'id_producto' => $producto->id_producto,
            'pxf_cantidad' => $cantidad,
            'pxf_precio_venta' => $producto->pro_precio_venta,
            'pxf_subtotal_producto' =>
                $producto->pro_precio_venta * $cantidad,
            'estado_pxf' => 'ABI',
        ]);
    }
}
