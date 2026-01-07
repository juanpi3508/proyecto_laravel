<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';
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

    // ==========================
    // ACCESORES
    // ==========================

    public function getImageUrlAttribute()
    {
        if (!$this->pro_imagen) return 'https://via.placeholder.com/600x600?text=Sin+imagen';

        $path = trim($this->pro_imagen);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        return asset('storage/' . $path);
    }

    public function getPrecioAttribute()
    {
        return $this->pro_precio_venta ?? 0;
    }

    public function getPrecioAnteriorAttribute()
    {
        return $this->precio > 0 ? $this->precio / 0.85 : 0;
    }

    public function getCategoriaNombreAttribute()
    {
        return $this->categoria->cat_descripcion ?? 'Sin categoría';
    }

    public function getStockAttribute()
    {
        return max(0, $this->pro_saldo_fin ?? 0);
    }

    public function getTokenAttribute()
    {
        return Crypt::encryptString($this->id_producto);
    }

    public static function masVendidos($limit = 6)
    {
        return self::select(
            'productos.*',
            DB::raw('SUM(pxf.pxf_cantidad) as total_vendido')
        )
            ->join('proxfac as pxf', 'productos.id_producto', '=', 'pxf.id_producto')
            ->join('facturas as f', 'f.id_factura', '=', 'pxf.id_factura')
            ->where('f.estado_fac', 'APR')
            ->where('pxf.estado_pxf', 'APR')
            ->groupBy('productos.id_producto')
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }
}
