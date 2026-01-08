<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Factura;

class FacturaController extends Controller
{
    public function generarFactura()
    {
        $usuario = Auth::user();

        try {
            $factura = Factura::generarDesdeCarrito(
                $usuario,
                session(config('facturas.session_carrito'), [])
            );

            return redirect()
                ->route('carrito.index')
                ->with([
                    'factura_confirmada' => true,
                    'id_factura' => $factura->id_factura,
                ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmar(string $idFactura)
    {
        $factura = Factura::findOrFail($idFactura);

        if ($factura->estado_fac !== config('facturas.estados.abierta')) {
            return redirect()->route('factura.show', $idFactura);
        }

        return view('facturas.confirmar', compact('factura'));
    }

    public function aprobar(string $idFactura)
    {
        try {
            $mensaje = Factura::aprobarPorFuncion($idFactura);

            session()->forget(config('facturas.session_carrito'));

            return redirect()
                ->route('catalogo.index')
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function listarFacturas()
    {
        $usuario = Auth::user();

        if (!$usuario->cliente) {
            return view('consultas.consulta_general', [
                'facturas' => [],
                'mensaje' => config('facturas.mensajes.sin_cliente'),
            ]);
        }

        $facturas = Factura::ecoPorCliente($usuario->cliente->id_cliente);

        return view('consultas.consulta_general', [
            'facturas' => $facturas,
            'mensaje' => $facturas->isEmpty()
                ? config('facturas.mensajes.sin_compras')
                : null,
        ]);
    }

    public function detallePopup(string $idFactura)
    {
        $factura = Factura::detalleSeguroParaUsuario(
            Auth::user(),
            $idFactura
        );

        return view('consultas.detalle_factura_modal', compact('factura'));
    }

    public function productosMasVendidos()
    {
        return Factura::productosMasVendidos();
    }
}
