<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CarritoController extends Controller
{
    public function index(Request $request)
    {
        $carrito = $this->getCarrito($request);
        $items = $this->hydrateProductos($carrito);
        $subtotal = $this->calcularSubtotal($items);
        $iva = 0.12;
        $total = $subtotal + ($subtotal * $iva);

        return view('carrito.index', compact(
            'items',
            'subtotal',
            'iva',
            'total'
        ));
    }

    public function store(Request $request)
    {
        $redirect = $request->input('redirect', false);

        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1'
        ]);

        $producto = Product::findOrFail($request->id_producto);
        $stock = max(0, $producto->pro_saldo_fin ?? 0);

        if ($stock === 0) {
            return back()->with(
                'mensaje_stock',
                'Este producto se encuentra agotado.'
            );
        }

        $cantidadSolicitada = (int) $request->cantidad;
        $carrito = $this->getCarrito($request);

        $cantidadEnCarrito = $carrito[$producto->id_producto]['cantidad'] ?? 0;
        $cantidadTotal = $cantidadEnCarrito + $cantidadSolicitada;

        if ($cantidadTotal > $stock) {
            return back()->with(
                'mensaje_stock',
                "Stock insuficiente. Disponible: {$stock}. En carrito: {$cantidadEnCarrito}."
            );
        }

        // Agregar / actualizar producto
        $carrito[$producto->id_producto] = [
            'id_producto' => $producto->id_producto,
            'cantidad' => $cantidadTotal
        ];

        $this->saveCarrito($request, $carrito);

        if ($redirect) {
            return redirect()
                ->route('carrito.index')
                ->with('success', 'Producto agregado al carrito.');
        }

        return back()->with('success', 'Producto agregado correctamente.');
    }


    public function update(Request $request, string $idProducto)
    {
        $request->validate([
            'cantidad' => 'required|integer'
        ]);

        $producto = Product::findOrFail($idProducto);
        $stock = max(0, $producto->pro_saldo_fin ?? 0);

        $cantidadSolicitada = (int)$request->cantidad;

        $cantidadFinal = min(
            max(1, $cantidadSolicitada),
            $stock
        );

        $carrito = $this->getCarrito($request);

        if (isset($carrito[$idProducto])) {
            $carrito[$idProducto]['cantidad'] = $cantidadFinal;
            $this->saveCarrito($request, $carrito);
        }


        if ($cantidadSolicitada > $stock) {
            return redirect()
                ->route('carrito.index')
                ->with('mensaje_stock',
                    'No existe stock suficiente para aumentar la cantidad. Se ajustó al máximo permitido.'
                );
        }

        if ($cantidadSolicitada <= 0) {
            return redirect()
                ->route('carrito.index')
                ->with('mensaje_stock',
                    'La cantidad mínima permitida es 1. Se ajustó automáticamente.'
                );
        }

        return redirect()->route('carrito.index');
    }


    public function destroy(Request $request, string $idProducto)
    {
        $carrito = $this->getCarrito($request);

        if (isset($carrito[$idProducto])) {
            unset($carrito[$idProducto]);
            $this->saveCarrito($request, $carrito);
        }

        return redirect()->route('carrito.index');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('carrito');
        return redirect()->route('carrito.index');
    }

    private function getCarrito(Request $request): array
    {
        return $request->session()->get('carrito', []);
    }

    private function saveCarrito(Request $request, array $carrito): void
    {
        $request->session()->put('carrito', $carrito);
    }

    private function hydrateProductos(array $carrito)
    {
        if (empty($carrito)) {
            return collect();
        }

        $productos = Product::whereIn(
            'id_producto',
            array_keys($carrito)
        )->get();

        return $productos->map(function ($producto) use ($carrito) {

            $cantidad = min(
                max(1, $carrito[$producto->id_producto]['cantidad']),
                max(0, $producto->pro_saldo_fin ?? 0)
            );

            return (object)[
                'id_producto' => $producto->id_producto,
                'descripcion' => $producto->pro_descripcion,
                'precio'      => $producto->pro_precio_venta,
                'imagen'      => $producto->pro_imagen,
                'cantidad'    => $cantidad,
                'stock'       => max(0, $producto->pro_saldo_fin ?? 0),
                'subtotal'    => $cantidad * $producto->pro_precio_venta
            ];
        });
    }

    private function calcularSubtotal($items): float
    {
        return $items->sum('subtotal');
    }
}
