<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Constants\ProductColumns as Col;

class CartService
{
    public function get(Request $request): Carrito
    {
        $data = collect($request->session()->get(config('carrito.session_key'), []));

        if ($data->isEmpty()) {
            return new Carrito();
        }

        $productos = Product::whereIn(Col::PK, $data->keys())
            ->get()
            ->keyBy(Col::PK);

        $items = $data->map(function ($row) use ($productos) {
            $producto = $productos[$row[Col::PK]];

            return new DetalleCarrito(
                (string) $producto->getKey(),
                $producto->normalizarCantidad((int) $row['cantidad']),
                $producto->precioVenta(),
                $producto->stockDisponible(),
                $producto->{Col::DESCRIPCION},
                $producto->image_url
            );
        });

        return new Carrito($items);
    }

    public function put(Request $request, Carrito $carrito): void
    {
        $request->session()->put(
            config('carrito.session_key'),
            $carrito->items()->mapWithKeys(fn ($item) => [
                $item->id_producto => [
                    Col::PK    => $item->id_producto,
                    'cantidad' => $item->cantidad,
                ],
            ])->toArray()
        );
    }

    public function cantidadEnCarrito(Carrito $carrito, string $idProducto): int
    {
        $item = $carrito->items()->firstWhere(Col::PK, $idProducto);
        return $item ? (int) $item->cantidad : 0;
    }

    /**
     * Devuelve null si ok, o un string con el mensaje de error de stock.
     */
    public function add(Request $request, Product $producto, int $cantidad): ?string
    {
        if ($producto->estaAgotado()) {
            return config('carrito.messages.agotado');
        }

        $carrito = $this->get($request);

        $productoId = (string) $producto->getKey();
        $cantidadActual = $this->cantidadEnCarrito($carrito, $productoId);
        $cantidadTotal  = $cantidadActual + $cantidad;

        if ($cantidadTotal > $producto->stockDisponible()) {
            return str_replace(
                ':stock',
                $producto->stockDisponible(),
                config('carrito.messages.stock_insuficiente_disponible')
            );
        }

        $detalle = new DetalleCarrito(
            $productoId,
            $cantidad,
            $producto->precioVenta(),
            $producto->stockDisponible(),
            $producto->{Col::DESCRIPCION},
            $producto->image_url
        );

        $carrito->agregarProducto($detalle);
        $this->put($request, $carrito);

        return null;
    }

    /**
     * Devuelve null si la cantidad quedó exacta,
     * o un mensaje de advertencia si se normalizó por min/stock.
     */
    public function updateCantidad(Request $request, Product $producto, int $cantidadSolicitada): ?string
    {
        $carrito = $this->get($request);

        $cantidadFinal = $producto->normalizarCantidad($cantidadSolicitada);

        foreach ($carrito->items() as $item) {
            if ($item->id_producto === (string) $producto->getKey()) {
                $item->actualizarCantidad($cantidadFinal);
                break;
            }
        }

        $this->put($request, $carrito);

        if ($cantidadFinal !== $cantidadSolicitada) {
            if ($cantidadSolicitada < config('carrito.cantidad.min')) {
                return str_replace(
                    ':min',
                    config('carrito.cantidad.min'),
                    config('carrito.messages.cantidad_minima')
                );
            }
            return config('carrito.messages.stock_insuficiente');
        }

        return null;
    }

    public function remove(Request $request, string $idProducto): void
    {
        $carrito = $this->get($request);

        if ($carrito->items()->firstWhere(Col::PK, $idProducto)) {
            $carrito->eliminarProducto($idProducto);
            $this->put($request, $carrito);
        }
    }

    public function clear(Request $request): bool
    {
        if ($request->session()->has(config('carrito.session_key'))) {
            $request->session()->forget(config('carrito.session_key'));
            return true;
        }
        return false;
    }
}
