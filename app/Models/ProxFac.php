<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxFac extends Model
{
    protected $table = 'proxfac';

    public $incrementing = false;
    public $timestamps = false;

    protected $primaryKey = null; // clave compuesta (no soportada nativamente)

    protected $fillable = [
        'id_factura',
        'id_producto',
        'pxf_cantidad',
        'pxf_precio_venta',
        'pxf_subtotal_producto',
        'estado_pxf'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }


    public function producto()
    {
        return $this->belongsTo(Product::class, 'id_producto', 'id_producto');
    }
}
