<?php

namespace App\Constants;

final class FacturaColumns
{
    public const TABLE = 'facturas';
    public const ID       = 'id_factura';
    public const CLIENTE  = 'id_cliente';
    public const SUBTOTAL = 'fac_subtotal';
    public const IVA      = 'fac_iva';
    public const TOTAL    = 'fac_total';
    public const TIPO     = 'fac_tipo';
    public const ESTADO   = 'estado_fac';
    public const FECHA    = 'fac_fecha_hora';
}
