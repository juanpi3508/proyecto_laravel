<?php

namespace App\Models;

use App\Constants\ClienteColumns as Col;
use App\Constants\FacturaColumns as FacCol;
use App\Constants\UsuarioColumns as UsuCol;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = Col::TABLE;
    protected $primaryKey = Col::PK;

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        Col::PK,
        Col::NOMBRE,
        Col::RUC_CED,
        Col::TELEFONO,
        Col::MAIL,
        Col::DIRECCION,
        Col::CIUDAD_ID,
        Col::ESTADO,
    ];

    protected $casts = [
        Col::PK => 'string',
        Col::CIUDAD_ID => 'string',
    ];

    /**
     * Relación: un cliente puede tener muchos usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(
            Usuario::class,
            Col::PK,
            Col::PK
        );
    }

    /**
     * Relación: un cliente puede tener muchas facturas
     */
    public function facturas()
    {
        return $this->hasMany(
            Factura::class,
            Col::PK,
            Col::PK
        );
    }

    /**
     * Scope: clientes activos (si lo necesitas en consultas)
     */
    public function scopeActivos($query)
    {
        return $query->where(Col::ESTADO, Col::ESTADO_ACTIVO);
    }
}
