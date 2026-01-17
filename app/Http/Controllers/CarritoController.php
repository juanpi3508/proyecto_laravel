<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Constants\ProductColumns as Col;
use App\Services\CartService;

class CarritoController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request)
    {
        $carrito = $this->cart->get($request);

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

        $mensajeStock = $this->cart->add($request, $producto, (int) $request->cantidad);

        if ($mensajeStock) {
            return back()->with('mensaje_stock', $mensajeStock);
        }

        return $request->boolean('redirect')
            ? redirect()->route('carrito.index')->with('success', config('carrito.messages.agregado'))
            : back()->with('success', config('carrito.messages.agregado'));
    }

    public function update(Request $request, string $idProducto)
    {
        $request->validate(['cantidad' => 'required|integer']);

        $producto = Product::findOrFail($idProducto);

        $payload = $this->cart->updateCantidadPayload(
            $request,
            $producto,
            (int) $request->cantidad
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        // comportamiento normal (no ajax)
        if ($payload['warning']) {
            return redirect()->route('carrito.index')->with('mensaje_stock', $payload['warning']);
        }

        return redirect()->route('carrito.index')->with('success', config('carrito.messages.cantidad_actualizada'));
    }

    public function destroy(Request $request, string $idProducto)
    {
        $this->cart->remove($request, $idProducto);

        return redirect()
            ->route('carrito.index')
            ->with('success', config('carrito.messages.producto_eliminado'));
    }

    public function clear(Request $request)
    {
        $vacio = $this->cart->clear($request);

        return redirect()
            ->route('carrito.index')
            ->with('success', $vacio
                ? config('carrito.messages.carrito_vaciado')
                : config(key: 'carrito.messages.carrito_vaciado') // o nada, como tú lo tenías
            );
    }
}
