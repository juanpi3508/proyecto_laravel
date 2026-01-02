<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'pro_descripcion',
        'pro_valor_compra',
        'pro_precio_venta',
        'pro_saldo_inicial',
        'pro_qty_ingresos',
        'pro_qty_egresos',
        'pro_qty_ajustes',
        'pro_saldo_final',
        'estado_prod',
        'pro_imagen',
        'id_categoria'
    ];
// Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(
            Category::class,
            'id_categoria',   // FK en products
            'id_categoria'    // PK en categorias
        );
    }

    protected $casts = [
        'id_categoria' => 'string',
        'id_producto'  => 'string',
    ];

    public function detallesCarrito()
    {
        return $this->hasMany(
            DetalleCarrito::class,
            'id_producto',
            'id_producto'
        );
    }

    public function detallesFactura()
    {
        return $this->hasMany(ProxFac::class, 'id_producto', 'id_producto');
    }

}

