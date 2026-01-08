<?php

namespace App\Constants;

final class UsuarioColumns
{
    public const TABLE = 'usuarios';
    public const PK = 'id_usuario';

    public const USERNAME = 'usu_usuario';
    public const PASSWORD = 'usu_contrasena';
    public const ESTADO   = 'estado_usu';

    public const INTENTOS = 'usu_intentos_fallidos';
    public const ULTIMO_ACCESO = 'usu_ultimo_acceso';

    public const ESTADO_ACTIVO = 'ACT';
    public const ESTADO_BLOQUEADO = 'BLQ';
}
