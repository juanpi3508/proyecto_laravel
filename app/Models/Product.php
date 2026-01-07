<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

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
        'estado_prod',
        'pro_imagen',
        'id_categoria'
    ];


    protected $guarded = [
        'pro_saldo_fin'
    ];

    protected $casts = [
        'id_producto'  => 'string',
        'id_categoria' => 'string',
    ];


    public function categoria()
    {
        return $this->belongsTo(
            Category::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public function detallesFactura()
    {
        return $this->hasMany(
            ProxFac::class,
            'id_producto',
            'id_producto'
        );
    }



    public function getImageUrlAttribute(): string
    {
        if (!$this->pro_imagen) {
            return 'https://via.placeholder.com/600x600?text=Sin+imagen';
        }

        $path = trim($this->pro_imagen);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getPrecioAttribute(): float
    {
        return (float) ($this->pro_precio_venta ?? 0);
    }

    public function getPrecioAnteriorAttribute(): float
    {
        return $this->precio > 0 ? round($this->precio / 0.85, 2) : 0;
    }

    public function getCategoriaNombreAttribute(): string
    {
        return $this->categoria->cat_descripcion ?? 'Sin categoría';
    }


    public function getStockAttribute(): int
    {
        return max(0, (int) ($this->pro_saldo_fin ?? 0));
    }

    public function getTokenAttribute(): string
    {
        return Crypt::encryptString($this->id_producto);
    }


    public function stockDisponible(): int
    {
        return $this->stock;
    }

    public function normalizarCantidad(int $cantidad): int
    {
        return min(
            max(1, $cantidad),
            $this->stockDisponible()
        );
    }

    public function precioVenta(): float
    {
        return $this->precio;
    }

    public function estaAgotado(): bool
    {
        return $this->stockDisponible() === 0;
    }

    public function puedeComprar(int $cantidadSolicitada, int $cantidadEnCarrito = 0): bool
    {
        return ($cantidadSolicitada + $cantidadEnCarrito) <= $this->stockDisponible();
    }


    public function scopeActivos($query)
    {
        return $query->where('estado_prod', 'ACT');
    }

    public function scopeBuscar($query, $q)
    {
        if (!empty($q)) {
            $q = mb_strtolower($q);
            $query->whereRaw('LOWER(pro_descripcion) LIKE ?', ['%' . $q . '%']);
        }

        return $query;
    }

    public function scopeFiltrarCategoria($query, $cat)
    {
        if (!empty($cat)) {
            $query->where('id_categoria', $cat);
        }

        return $query;
    }

    public function scopeOrdenar($query, $sort)
    {
        return match ($sort) {
            'price-asc'  => $query->orderBy('pro_precio_venta', 'asc'),
            'price-desc' => $query->orderBy('pro_precio_venta', 'desc'),
            'name-asc'   => $query->orderBy('pro_descripcion', 'asc'),
            'name-desc'  => $query->orderBy('pro_descripcion', 'desc'),
            default      => $query->orderBy('id_producto', 'desc'),
        };
    }


    public static function obtenerParaCarrito(array $carrito)
    {
        $ids = collect($carrito)
            ->pluck('id_producto')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw new \Exception('Carrito inválido.');
        }

        return self::whereIn('id_producto', $ids)
            ->get()
            ->keyBy('id_producto');
    }

    public static function masVendidos(int $limit = 6)
    {
        return self::select(
            'productos.*',
            DB::raw('SUM(pxf.pxf_cantidad) AS total_vendido')
        )
            ->join('proxfac as pxf', 'productos.id_producto', '=', 'pxf.id_producto')
            ->join('facturas as f', 'f.id_factura', '=', 'pxf.id_factura')
            ->where('productos.estado_prod', 'ACT')
            ->where('f.estado_fac', 'APR')
            ->where('pxf.estado_pxf', 'APR')
            ->groupBy('productos.id_producto')
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }
    public function scopePublico($query)
    {
        return $query->with('categoria')->activos();
    }

    /**
     * Catalogo completo aplicando scopes existentes.
     */
    public static function catalogo(?string $q, ?string $cat, string $sort = 'relevance')
    {
        return static::publico()
            ->buscar($q)
            ->filtrarCategoria($cat)
            ->ordenar($sort)
            ->get();
    }

    /**
     * Buscar un producto "publico" por token.
     */
    public static function findByTokenOrFail(string $token): self
    {
        try {
            $id = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            abort(404);
        }

        return static::publico()
            ->whereKey((string) $id) // usa primaryKey definido: id_producto
            ->firstOrFail();
    }

    /**
     * Productos relacionados por categoria (excluye el actual).
     */
    public static function relacionados(self $producto, int $limit = 4)
    {
        return static::publico()
            ->where('id_categoria', $producto->id_categoria)
            ->whereKeyNot($producto->getKey())
            ->limit($limit)
            ->get();
    }
}
