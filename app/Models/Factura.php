<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Factura extends Model
{
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_factura',
        'id_cliente',
        'fac_subtotal',
        'fac_iva',
        'fac_total',
        'fac_tipo',
        'estado_fac'
    ];

    /* ===================== RELACIONES ===================== */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(ProxFac::class, 'id_factura');
    }

    /* ===================== FACTURACIÓN ===================== */

    public static function generarDesdeCarrito($usuario, array $carrito): self
    {
        if (!$usuario || !$usuario->cliente) {
            throw new \Exception('Debe completar sus datos de cliente para facturar.');
        }

        if (empty($carrito)) {
            throw new \Exception('El carrito está vacío.');
        }

        $productos = Product::obtenerParaCarrito($carrito);

        [$subtotal, $iva, $total] = self::calcularTotales($productos, $carrito);

        return DB::transaction(function () use (
            $usuario,
            $productos,
            $carrito,
            $subtotal,
            $iva,
            $total
        ) {

            $idFactura = DB::selectOne(
                'SELECT GenerarCodigoFactura() AS codigo'
            )->codigo;

            $factura = self::create([
                'id_factura' => $idFactura,
                'id_cliente' => $usuario->cliente->id_cliente,
                'fac_subtotal' => $subtotal,
                'fac_iva' => $iva,
                'fac_total' => $total,
                'fac_tipo' => 'ECO',
                'estado_fac' => 'ABI',
            ]);

            $factura->crearDetallesDesdeCarrito($productos, $carrito);

            return $factura;
        });
    }

    /* ===================== CÁLCULOS ===================== */

    private static function calcularTotales($productos, $carrito): array
    {
        $subtotal = 0;

        foreach ($carrito as $item) {
            if (!isset($productos[$item['id_producto']])) {
                throw new \Exception('Producto inválido en el carrito.');
            }

            $subtotal +=
                $productos[$item['id_producto']]->pro_precio_venta
                * $item['cantidad'];
        }

        $iva = round($subtotal * 0.12, 2);

        return [$subtotal, $iva, $subtotal + $iva];
    }

    /* ===================== DETALLES ===================== */

    private function crearDetallesDesdeCarrito($productos, $carrito): void
    {
        foreach ($carrito as $item) {
            ProxFac::crearDesdeProducto(
                $this->id_factura,
                $productos[$item['id_producto']],
                $item['cantidad']
            );
        }
    }

    /* ===================== APROBACIÓN ===================== */

    public static function aprobarPorFuncion(string $idFactura): string
    {
        $resultado = DB::selectOne(
            "SELECT fn_aprobar_factura_json(?) AS resultado",
            [$idFactura]
        );

        $json = json_decode($resultado->resultado, true);

        if (!$json || !$json['ok']) {
            throw new \Exception($json['mensaje'] ?? 'No se pudo aprobar la factura');
        }

        return $json['mensaje'];
    }

    /* ===================== CONSULTAS ===================== */

    public static function ecoPorCliente(string $idCliente)
    {
        return self::where('id_cliente', $idCliente)
            ->where('fac_tipo', 'ECO')
            ->orderByDesc('fac_fecha_hora')
            ->get();
    }

    public static function detalleSeguroParaUsuario($usuario, string $idFactura): self
    {
        if (!$usuario->cliente) {
            abort(403);
        }

        return self::with('detalles.producto')
            ->where('id_factura', $idFactura)
            ->where('id_cliente', $usuario->cliente->id_cliente)
            ->firstOrFail();
    }

    public static function productosMasVendidos()
    {
        return DB::table('proxfac as pxf')
            ->join('productos as p', 'p.id_producto', '=', 'pxf.id_producto')
            ->join('facturas as f', 'f.id_factura', '=', 'pxf.id_factura')
            ->where('f.estado_fac', 'APR')
            ->where('pxf.estado_pxf', 'APR')
            ->groupBy(
                'p.id_producto',
                'p.pro_descripcion',
                'p.pro_precio_venta',
                'p.pro_imagen'
            )
            ->select(
                'p.*',
                DB::raw('SUM(pxf.pxf_cantidad) AS total_vendido')
            )
            ->orderByDesc('total_vendido')
            ->limit(6)
            ->get();
    }
}
