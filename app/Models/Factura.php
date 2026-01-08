<?php

namespace App\Models;

use App\Constants\FacturaColumns as Col;
use App\Constants\ProductColumns as ProdCol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Factura extends Model
{
    protected $table = Col::TABLE;
    protected $primaryKey = Col::ID;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        Col::ID,
        Col::CLIENTE,
        Col::SUBTOTAL,
        Col::IVA,
        Col::TOTAL,
        Col::TIPO,
        Col::ESTADO,
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, Col::CLIENTE);
    }

    public function detalles()
    {
        return $this->hasMany(ProxFac::class, Col::ID);
    }

    public static function generarDesdeCarrito($usuario, array $carrito): self
    {
        if (!$usuario || !$usuario->cliente) {
            throw new \Exception(config('facturas.mensajes.sin_cliente'));
        }

        if (empty($carrito)) {
            throw new \Exception(config('facturas.mensajes.carrito_vacio'));
        }

        $productos = Product::obtenerParaCarrito($carrito);

        [$subtotal, $iva, $total] = self::calcularTotales($productos, $carrito);

        return DB::transaction(function () use (
            $usuario, $productos, $carrito, $subtotal, $iva, $total
        ) {

            $codigo = DB::selectOne(
                'SELECT ' . config('facturas.db.fn_generar_codigo') . '() AS codigo'
            )->codigo;

            $factura = self::create([
                Col::ID       => $codigo,
                Col::CLIENTE  => $usuario->cliente->id_cliente,
                Col::SUBTOTAL => $subtotal,
                Col::IVA      => $iva,
                Col::TOTAL    => $total,
                Col::TIPO     => config('facturas.tipos.eco'),
                Col::ESTADO   => config('facturas.estados.abierta'),
            ]);

            $factura->crearDetallesDesdeCarrito($productos, $carrito);

            return $factura;
        });
    }

    private static function calcularTotales($productos, $carrito): array
    {
        $subtotal = 0;

        foreach ($carrito as $item) {
            $idProducto = $item[ProdCol::PK] ?? null;

            if (!$idProducto || !isset($productos[$idProducto])) {
                throw new \Exception(config('facturas.mensajes.producto_invalido'));
            }

            $cantidad = (int) ($item['cantidad'] ?? 0);

            $subtotal +=
                ((float) $productos[$idProducto]->precioVenta())
                * $cantidad;
        }

        $iva = round($subtotal * config('facturas.iva'), 2);

        return [$subtotal, $iva, $subtotal + $iva];
    }

    private function crearDetallesDesdeCarrito($productos, $carrito): void
    {
        foreach ($carrito as $item) {
            $idProducto = $item[ProdCol::PK];

            ProxFac::crearDesdeProducto(
                $this->{Col::ID},
                $productos[$idProducto],
                (int) $item['cantidad']
            );
        }
    }

    public static function aprobarPorFuncion(string $idFactura): string
    {
        $resultado = DB::selectOne(
            'SELECT ' . config('facturas.db.fn_aprobar') . '(?) AS resultado',
            [$idFactura]
        );

        $json = json_decode($resultado->resultado, true);

        if (!$json || !$json['ok']) {
            throw new \Exception(
                $json['mensaje'] ?? config('facturas.mensajes.aprobacion_error')
            );
        }

        return $json['mensaje'];
    }

    public static function ecoPorCliente(string $idCliente)
    {
        return self::where(Col::CLIENTE, $idCliente)
            ->where(Col::TIPO, config('facturas.tipos.eco'))
            ->orderByDesc(Col::FECHA)
            ->get();
    }

    public static function detalleSeguroParaUsuario($usuario, string $idFactura): self
    {
        if (!$usuario->cliente) {
            abort(403);
        }

        return self::with('detalles.producto')
            ->where(Col::ID, $idFactura)
            ->where(Col::CLIENTE, $usuario->cliente->id_cliente)
            ->firstOrFail();
    }
}
