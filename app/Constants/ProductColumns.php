<?php

namespace App\Constants;

final class ProductColumns
{
    // Table / PK
    public const TABLE = 'productos';
    public const PK = 'id_producto';

    // Columns
    public const DESCRIPCION     = 'pro_descripcion';
    public const VALOR_COMPRA    = 'pro_valor_compra';
    public const PRECIO_VENTA    = 'pro_precio_venta';
    public const SALDO_INICIAL   = 'pro_saldo_inicial';
    public const QTY_INGRESOS    = 'pro_qty_ingresos';
    public const QTY_EGRESOS     = 'pro_qty_egresos';
    public const QTY_AJUSTES     = 'pro_qty_ajustes';
    public const ESTADO          = 'estado_prod';
    public const IMAGEN          = 'pro_imagen';
    public const CATEGORIA_ID    = 'id_categoria';
    public const SALDO_FINAL     = 'pro_saldo_fin';

    // Values
    public const ESTADO_ACTIVO = 'ACT';
}
