<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Factura;
use App\Constants\FacturaColumns as Col;

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
                    'id_factura' => $factura->getKey(),
                ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmar(string $idFactura)
    {
        $factura = Factura::findOrFail($idFactura);

        if ($factura->{Col::ESTADO} !== config('facturas.estados.abierta')) {
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

    /**
     * Procesa el pago: genera la factura y la aprueba en una sola operación.
     * Se usa via AJAX desde el modal de pago.
     */
    public function procesarPago()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => config('auth_messages.errors.session_required'),
            ], 401);
        }

        try {
            // Delegar toda la lógica al modelo
            $resultado = Factura::procesarPagoCompleto(
                $usuario,
                session(config('facturas.session_carrito'), [])
            );

            // Limpiar el carrito de sesión
            session()->forget(config('facturas.session_carrito'));

            return response()->json([
                'success'    => true,
                'message'    => $resultado['mensaje'],
                'id_factura' => $resultado['factura_id'],
                'redirect'   => route('facturas.historial', ['mostrar' => $resultado['factura_id']]),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
