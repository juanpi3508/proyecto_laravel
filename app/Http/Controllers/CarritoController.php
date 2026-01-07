<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use Illuminate\Support\Collection;

class CarritoController extends Controller
{

    public function index(Request $request)
    {
        $carrito = $this->construirCarritoDesdeSesion($request);

        return view('carrito.index', [
            'items'      => $carrito->items(),
            'subtotal'   => $carrito->subtotal(),
            'iva'        => $carrito->iva(),
            'impuestos'  => $carrito->impuestos(),
            'total'      => $carrito->total(),
            'articulos'  => $carrito->totalArticulos(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad'    => 'required|integer|min:1'
        ]);

        $producto = Product::findOrFail($request->id_producto);

        if ($producto->estaAgotado()) {
            return back()->with(
                'mensaje_stock',
                'Este producto se encuentra agotado.'
            );
        }

        $carrito = $this->construirCarritoDesdeSesion($request);

        $cantidadActual = $this->cantidadEnCarrito($carrito, $producto->id_producto);
        $total = $cantidadActual + $request->cantidad;

        if ($total > $producto->stockDisponible()) {
            return back()->with(
                'mensaje_stock',
                "Stock insuficiente. Disponible: {$producto->stockDisponible()}."
            );
        }

        $detalle = new DetalleCarrito(
            $producto->id_producto,
            $request->cantidad,
            $producto->precioVenta(),
            $producto->stockDisponible(),
            $producto->pro_descripcion,
            $producto->image_url
        );

        $carrito->agregarProducto($detalle);

        $this->guardarCarritoEnSesion($request, $carrito);

        return $request->boolean('redirect')
            ? redirect()->route('carrito.index')->with('success', 'Producto agregado al carrito.')
            : back()->with('success', 'Producto agregado correctamente.');
    }



    public function update(Request $request, string $idProducto)
    {
        $request->validate([
            'cantidad' => 'required|integer'
        ]);

        $producto = Product::findOrFail($idProducto);
        $carrito  = $this->construirCarritoDesdeSesion($request);

        $cantidadFinal = $producto->normalizarCantidad($request->cantidad);

        foreach ($carrito->items() as $item) {
            if ($item->id_producto === $idProducto) {
                $item->actualizarCantidad($cantidadFinal);
                break;
            }
        }

        $this->guardarCarritoEnSesion($request, $carrito);

        if ($cantidadFinal !== (int) $request->cantidad) {
            return redirect()
                ->route('carrito.index')
                ->with(
                    'mensaje_stock',
                    'La cantidad fue ajustada según el stock disponible.'
                );
        }

        return redirect()->route('carrito.index');
    }


    public function destroy(Request $request, string $idProducto)
    {
        $carrito = $this->construirCarritoDesdeSesion($request);
        $carrito->eliminarProducto($idProducto);

        $this->guardarCarritoEnSesion($request, $carrito);

        return redirect()->route('carrito.index');
    }


    public function clear(Request $request)
    {
        $request->session()->forget('carrito');
        return redirect()->route('carrito.index');
    }


    private function construirCarritoDesdeSesion(Request $request): Carrito
    {
        $data = collect($request->session()->get('carrito', []));

        if ($data->isEmpty()) {
            return new Carrito();
        }

        $productos = Product::whereIn(
            'id_producto',
            $data->keys()
        )->get()->keyBy('id_producto');

        $items = $data->map(function ($row) use ($productos) {

            $producto = $productos[$row['id_producto']];

            return new DetalleCarrito(
                $producto->id_producto,
                $producto->normalizarCantidad($row['cantidad']),
                $producto->precioVenta(),
                $producto->stockDisponible(),
                $producto->pro_descripcion,
                $producto->image_url
            );
        });

        return new Carrito($items);
    }

    private function guardarCarritoEnSesion(Request $request, Carrito $carrito): void
    {
        $request->session()->put(
            'carrito',
            $carrito->items()->mapWithKeys(fn ($item) => [
                $item->id_producto => [
                    'id_producto' => $item->id_producto,
                    'cantidad'    => $item->cantidad
                ]
            ])->toArray()
        );
    }

    private function cantidadEnCarrito(Carrito $carrito, string $idProducto): int
    {
        $item = $carrito->items()->firstWhere('id_producto', $idProducto);
        return $item ? $item->cantidad : 0;
    }
}
