<?php

namespace App\Models;

use App\Constants\FacturaColumns as FacCol;
use App\Constants\ProductColumns as Col;
use App\Constants\ProxFacColumns as PxfCol;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Product extends Model
{
    protected $table = Col::TABLE;
    protected $primaryKey = Col::PK;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        Col::DESCRIPCION,
        Col::VALOR_COMPRA,
        Col::PRECIO_VENTA,
        Col::SALDO_INICIAL,
        Col::QTY_INGRESOS,
        Col::QTY_EGRESOS,
        Col::QTY_AJUSTES,
        Col::ESTADO,
        Col::IMAGEN,
        Col::CATEGORIA_ID,
    ];

    protected $guarded = [
        Col::SALDO_FINAL,
    ];

    protected $casts = [
        Col::PK           => 'string',
        Col::CATEGORIA_ID => 'string',
    ];

    public function categoria()
    {
        return $this->belongsTo(
            Category::class,
            Col::CATEGORIA_ID,
            Col::CATEGORIA_ID
        );
    }

    public function detallesFactura()
    {
        return $this->hasMany(
            ProxFac::class,
            Col::PK,
            Col::PK
        );
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->{Col::IMAGEN}) {
            return 'https://via.placeholder.com/600x600?text=Sin+imagen';
        }

        $path = trim($this->{Col::IMAGEN});

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getPrecioAttribute(): float
    {
        return (float) ($this->{Col::PRECIO_VENTA} ?? 0);
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
        return max(0, (int) ($this->{Col::SALDO_FINAL} ?? 0));
    }

    public function getTokenAttribute(): string
    {
        return Crypt::encryptString($this->getKey());
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
        return $query->where(Col::ESTADO, Col::ESTADO_ACTIVO);
    }

    public function scopeBuscar($query, $q)
    {
        if (!empty($q)) {
            $query->where(Col::DESCRIPCION, 'ILIKE', '%' . trim($q) . '%');
        }

        return $query;
    }

    public function scopeFiltrarCategoria($query, $cat)
    {
        if (!empty($cat)) {
            $query->where(Col::CATEGORIA_ID, $cat);
        }

        return $query;
    }

    public function scopeOrdenar($query, $sort)
    {
        return match ($sort) {
            'price-asc'  => $query->orderBy(Col::PRECIO_VENTA, 'asc'),
            'price-desc' => $query->orderBy(Col::PRECIO_VENTA, 'desc'),
            'name-asc'   => $query->orderBy(Col::DESCRIPCION, 'asc'),
            'name-desc'  => $query->orderBy(Col::DESCRIPCION, 'desc'),
            default      => $query->orderBy(Col::PK, 'desc'),
        };
    }

    public static function obtenerParaCarrito(array $carrito)
    {
        $ids = collect($carrito)
            ->pluck(Col::PK)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw new \Exception('Carrito inválido.');
        }

        return self::whereIn(Col::PK, $ids)
            ->get()
            ->keyBy(Col::PK);
    }

    public static function masVendidos(int $limit = 6)
    {
        $estadoAprobado = config('facturas.estados.aprobada');
        $tipoEco = config('facturas.tipos.eco');

        $filtroDetalles = function ($query) use ($estadoAprobado, $tipoEco) {
            $query->where(PxfCol::ESTADO, $estadoAprobado)
                ->whereHas('factura', function ($q) use ($estadoAprobado, $tipoEco) {
                    $q->where(FacCol::ESTADO, $estadoAprobado)
                      ->where(FacCol::TIPO, $tipoEco);
                });
        };

        return self::activos()
            ->whereHas('detallesFactura', $filtroDetalles)
            ->withSum(
                ['detallesFactura as total_vendido' => $filtroDetalles],
                PxfCol::CANTIDAD
            )
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }

    public function scopePublico($query)
    {
        return $query->with('categoria')->activos();
    }

    /**
     *
     * @param  array  $filters  ['q' => ?, 'cat' => ?, 'sort' => ?]
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function catalogo(array $filters = [])
    {
        // Normalizar filtros
        $q    = isset($filters['q']) ? trim((string) $filters['q']) : null;
        $cat  = $filters['cat']  ?? null;
        $sort = $filters['sort'] ?? 'relevance';

        // Aceptar solo sorts válidos
        $allowedSorts = [
            'relevance',
            'price-asc',
            'price-desc',
            'name-asc',
            'name-desc',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'relevance';
        }

        // Armar query usando scopes existentes
        $query = static::publico()
            ->buscar($q)
            ->filtrarCategoria($cat)
            ->ordenar($sort); // 'relevance' cae en el default del scopeOrdenar()

        // 16 productos por página ≈ 4 filas de 4 en desktop
        return $query
            ->paginate(16)
            ->withQueryString();
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
            ->whereKey((string) $id)
            ->firstOrFail();
    }

    /**
     * Productos relacionados por categoria (excluye el actual).
     */
    public static function relacionados(self $producto, int $limit = 4)
    {
        return static::publico()
            ->where(Col::CATEGORIA_ID, $producto->{Col::CATEGORIA_ID})
            ->whereKeyNot($producto->getKey())
            ->limit($limit)
            ->get();
    }
}
