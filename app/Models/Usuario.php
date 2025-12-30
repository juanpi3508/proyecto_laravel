<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // tu tabla no tiene created_at/updated_at

    protected $hidden = [
        'usu_contrasena',
    ];

    // Laravel buscará "password"; aquí le decimos que use usu_contrasena
    public function getAuthPassword()
    {
        return $this->usu_contrasena;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}

