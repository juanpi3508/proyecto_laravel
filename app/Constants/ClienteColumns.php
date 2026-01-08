<?php

namespace App\Constants;

final class ClienteColumns
{
    public const TABLE = 'clientes';
    public const PK = 'id_cliente';

    public const NOMBRE    = 'cli_nombre';
    public const RUC_CED   = 'cli_ruc_ced';
    public const TELEFONO  = 'cli_telefono';
    public const MAIL      = 'cli_mail';
    public const DIRECCION = 'cli_direccion';
    public const CIUDAD_ID = 'id_ciudad';
    public const ESTADO    = 'estado_cli';

    // Estados
    public const ESTADO_ACTIVO = 'ACT';
    public const ESTADO_INACTIVO = 'INA';
}
