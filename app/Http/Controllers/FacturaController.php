<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Factura;
use App\Models\ProxFac;
use App\Models\Product;

class FacturaController extends Controller
{
    public function generarFactura()
    {
        $usuario = Auth::user();

        if (!$usuario || !$usuario->cliente) {
            return back()->with('error', 'Debe completar sus datos de cliente para facturar.');
        }

        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return back()->with('error', 'El carrito está vacío.');
        }

        $idsProductos = [];

        foreach ($carrito as $item) {
            if (
                isset($item['id_producto']) &&
                is_string($item['id_producto']) &&
                trim($item['id_producto']) !== ''
            ) {
                $idsProductos[] = $item['id_producto'];
            }
        }

        if (empty($idsProductos)) {
            return back()->with('error', 'El carrito está vacío.');
        }

        $productos = Product::whereIn('id_producto', $idsProductos)
            ->get()
            ->keyBy('id_producto');

        $subtotal = 0;

        foreach ($carrito as $item) {
            if (!isset($productos[$item['id_producto']])) {
                return back()->with('error', 'Uno o más productos del carrito ya no existen.');
            }

            $producto = $productos[$item['id_producto']];
            $subtotal += $producto->pro_precio_venta * $item['cantidad'];
        }

        $iva = $subtotal * 0.12;
        $total = $subtotal + $iva;

        $cliente = $usuario->cliente;
        $idFactura = null;

        DB::transaction(function () use (
            &$idFactura,
            $cliente,
            $carrito,
            $productos,
            $subtotal,
            $iva,
            $total
        ) {
            $idFactura = DB::selectOne(
                'SELECT GenerarCodigoFactura() AS codigo'
            )->codigo;

            Factura::create([
                'id_factura' => $idFactura,
                'id_cliente' => $cliente->id_cliente,
                'fac_subtotal' => $subtotal,
                'fac_iva' => $iva,
                'fac_total' => $total,
                'fac_tipo' => 'ECO',
                'estado_fac' => 'ABI',
            ]);

            foreach ($carrito as $item) {
                $producto = $productos[$item['id_producto']];

                ProxFac::create([
                    'id_factura' => $idFactura,
                    'id_producto' => $item['id_producto'],
                    'pxf_cantidad' => $item['cantidad'],
                    'pxf_precio_venta' => $producto->pro_precio_venta,
                    'pxf_subtotal_producto' => $producto->pro_precio_venta * $item['cantidad'],
                    'estado_pxf' => 'ABI',
                ]);
            }
        });

        return redirect()
            ->route('carrito.index')
            ->with([
                'factura_confirmada' => true,
                'id_factura' => $idFactura
            ]);
    }

    public function confirmar($idFactura)
    {
        $factura = Factura::findOrFail($idFactura);

        if ($factura->estado_fac !== 'ABI') {
            return redirect()->route('factura.show', $idFactura);
        }

        return view('facturas.confirmar', compact('factura'));
    }

    public function aprobar($idFactura)
    {
        try {

            $resultado = DB::selectOne(
                "SELECT fn_aprobar_factura_json(?) AS resultado",
                [$idFactura]
            );


            $json = json_decode($resultado->resultado, true);


            if (!$json || !isset($json['ok'])) {
                throw new \Exception('Respuesta inválida del sistema de aprobación');
            }

            if ($json['ok'] === false) {
                return redirect()
                    ->back()
                    ->with('error', $json['mensaje'] ?? 'No se pudo aprobar la factura');
            }

            session()->forget('carrito');

            return redirect()
                ->route('catalogo.index')
                ->with('success', $json['mensaje']);

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->with('error', 'Error al aprobar factura: ' . $e->getMessage());
        }
    }


    public function listarFacturas()
    {
        $usuario = Auth::user();

        if (!$usuario->cliente) {
            return view('consultas.consulta_general', [
                'facturas' => [],
                'mensaje' => 'No existe información de cliente asociada.'
            ]);
        }

        $idCliente = $usuario->cliente->id_cliente;

        // Obtener solo facturas eco del cliente
        $facturas = Factura::obtenerEcoPorCliente($idCliente);

        // Flujo alterno: sin compras previas
        if ($facturas->isEmpty()) {
            return view('consultas.consulta_general', [
                'facturas' => [],
                'mensaje' => 'No existen compras previas registradas.'
            ]);
        }

        return view('consultas.consulta_general', [
            'facturas' => $facturas,
            'mensaje' => null
        ]);
    }
    public function detallePopup($idFactura)
    {
        $usuario = Auth::user()->fresh(['cliente']);

        if (!$usuario->cliente) {
            abort(403);
        }

        $factura = Factura::with(['detalles.producto'])
            ->where('id_factura', $idFactura)
            ->where('id_cliente', $usuario->cliente->id_cliente)
            ->firstOrFail();

        return view('consultas.detalle_factura_modal', compact('factura'));
    }
    public function productosMasVendidos()
    {
        return DB::table('proxfac as pxf')
            ->join('productos as p', 'p.id_producto', '=', 'pxf.id_producto')
            ->join('facturas as f', 'f.id_factura', '=', 'pxf.id_factura')
            ->select(
                'p.id_producto',
                'p.pro_descripcion',
                'p.pro_precio_venta',
                'p.pro_imagen',
                DB::raw('SUM(pxf.pxf_cantidad) as total_vendido')
            )
            ->where('f.estado_fac', 'APR')
            ->where('pxf.estado_pxf', 'APR')
            ->groupBy(
                'p.id_producto',
                'p.pro_descripcion',
                'p.pro_precio_venta',
                'p.pro_imagen'
            )
            ->orderByDesc('total_vendido')
            ->limit(6)
            ->get();
    }
}
