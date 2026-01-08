<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Constants\ProductColumns as Col;

class CarritoController extends Controller
{
    public function index(Request $request)
    {
        $carrito = $this->construirCarritoDesdeSesion($request);

        return view('carrito.index', [
            'items'     => $carrito->items(),
            'subtotal'  => $carrito->subtotal(),
            'iva'       => $carrito->iva(),
            'impuestos' => $carrito->impuestos(),
            'total'     => $carrito->total(),
            'articulos' => $carrito->totalArticulos(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            Col::PK => 'required|exists:' . Col::TABLE . ',' . Col::PK,
            'cantidad' => 'required|integer|min:' . config('carrito.cantidad.min'),
        ]);

        $producto = Product::findOrFail($request->input(Col::PK));

        if ($producto->estaAgotado()) {
            return back()->with(
                'mensaje_stock',
                config('carrito.messages.agotado')
            );
        }

        $carrito = $this->construirCarritoDesdeSesion($request);

        $productoId = (string) $producto->getKey();

        $cantidadActual = $this->cantidadEnCarrito($carrito, $productoId);
        $cantidadTotal  = $cantidadActual + (int) $request->cantidad;

        if ($cantidadTotal > $producto->stockDisponible()) {
            return back()->with(
                'mensaje_stock',
                str_replace(
                    ':stock',
                    $producto->stockDisponible(),
                    config('carrito.messages.stock_insuficiente_disponible')
                )
            );
        }

        $detalle = new DetalleCarrito(
            $productoId,
            (int) $request->cantidad,
            $producto->precioVenta(),
            $producto->stockDisponible(),
            $producto->{Col::DESCRIPCION},
            $producto->image_url
        );

        $carrito->agregarProducto($detalle);

        $this->guardarCarritoEnSesion($request, $carrito);

        return $request->boolean('redirect')
            ? redirect()
                ->route('carrito.index')
                ->with('success', config('carrito.messages.agregado'))
            : back()->with('success', config('carrito.messages.agregado'));
    }

    public function update(Request $request, string $idProducto)
    {
        $request->validate([
            'cantidad' => 'required|integer',
        ]);

        $producto = Product::findOrFail($idProducto);
        $carrito  = $this->construirCarritoDesdeSesion($request);

        $cantidadSolicitada = (int) $request->cantidad;
        $cantidadFinal = $producto->normalizarCantidad($cantidadSolicitada);

        foreach ($carrito->items() as $item) {
            if ($item->id_producto === $idProducto) {
                $item->actualizarCantidad($cantidadFinal);
                break;
            }
        }

        $this->guardarCarritoEnSesion($request, $carrito);

        if ($cantidadFinal !== $cantidadSolicitada) {

            if ($cantidadSolicitada < config('carrito.cantidad.min')) {
                return redirect()
                    ->route('carrito.index')
                    ->with(
                        'mensaje_stock',
                        str_replace(
                            ':min',
                            config('carrito.cantidad.min'),
                            config('carrito.messages.cantidad_minima')
                        )
                    );
            }

            return redirect()
                ->route('carrito.index')
                ->with(
                    'mensaje_stock',
                    config('carrito.messages.stock_insuficiente')
                );
        }

        return redirect()
            ->route('carrito.index')
            ->with(
                'success',
                config('carrito.messages.cantidad_actualizada')
            );
    }

    public function destroy(Request $request, string $idProducto)
    {
        $carrito = $this->construirCarritoDesdeSesion($request);

        if ($carrito->items()->firstWhere(Col::PK, $idProducto)) {
            $carrito->eliminarProducto($idProducto);
            $this->guardarCarritoEnSesion($request, $carrito);

            return redirect()
                ->route('carrito.index')
                ->with(
                    'success',
                    config('carrito.messages.producto_eliminado')
                );
        }

        return redirect()->route('carrito.index');
    }

    public function clear(Request $request)
    {
        if ($request->session()->has(config('carrito.session_key'))) {
            $request->session()->forget(config('carrito.session_key'));

            return redirect()
                ->route('carrito.index')
                ->with(
                    'success',
                    config('carrito.messages.carrito_vaciado')
                );
        }

        return redirect()->route('carrito.index');
    }

    private function construirCarritoDesdeSesion(Request $request): Carrito
    {
        $data = collect(
            $request->session()->get(config('carrito.session_key'), [])
        );

        if ($data->isEmpty()) {
            return new Carrito();
        }

        // keys() del session array son IDs de producto
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

    private function guardarCarritoEnSesion(Request $request, Carrito $carrito): void
    {
        $request->session()->put(
            config('carrito.session_key'),
            $carrito->items()->mapWithKeys(fn ($item) => [
                $item->id_producto => [
                    Col::PK     => $item->id_producto,
                    'cantidad'  => $item->cantidad,
                ],
            ])->toArray()
        );
    }

    private function cantidadEnCarrito(Carrito $carrito, string $idProducto): int
    {
        $item = $carrito->items()->firstWhere(Col::PK, $idProducto);
        return $item ? (int) $item->cantidad : 0;
    }
}
