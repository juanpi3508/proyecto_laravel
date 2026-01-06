<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fac_fecha_hora',
        'fac_subtotal',
        'fac_iva',
        'fac_total',
        'fac_tipo',
        'estado_fac'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(ProxFac::class, 'id_factura', 'id_factura')
            ->where('estado_pxf', 'APR');
    }

    public static function obtenerEcoPorCliente($idCliente)
    {
        return self::where('id_cliente', $idCliente)
            ->where('fac_tipo', 'ECO')
            ->orderBy('fac_fecha_hora', 'desc')
            ->get();
    }

    public static function obtenerEcoPorCliente($idCliente)
    {
        return self::where('id_cliente', $idCliente)
            ->where('fac_tipo', 'ECO')
            ->orderBy('fac_fecha_hora', 'desc')
            ->get();
    }

    public static function obtenerEcoPorCliente($idCliente)
    {
        return self::where('id_cliente', $idCliente)
            ->where('fac_tipo', 'ECO')
            ->orderBy('fac_fecha_hora', 'desc')
            ->get();
    }
}
