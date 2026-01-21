<?php

namespace App\Models;

use App\Constants\ClienteColumns as Col;
use App\Constants\FacturaColumns as FacCol;
use App\Constants\UsuarioColumns as UsuCol;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = Col::TABLE;       // 'clientes'
    protected $primaryKey = Col::PK;     // 'id_cliente'

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
    public static function buscarPorRucCed(string $ruc): ?self
    {
        return static::where(Col::RUC_CED, $ruc)->first();
    }

    protected $casts = [
        Col::PK        => 'string',
        Col::CIUDAD_ID => 'string',
    ];
    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, Col::CIUDAD_ID, 'id_ciudad');
    }

    /**
     * Relación: un cliente puede tener muchos usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(
            Usuario::class,
            'id_cliente',   // FK en usuarios
            Col::PK         // 'id_cliente' en clientes
        );
        // si luego agregas const CLIENTE_ID en UsuarioColumns:
        // return $this->hasMany(Usuario::class, UsuCol::CLIENTE_ID, Col::PK);
    }

    /**
     * Relación: un cliente puede tener muchas facturas
     */
    public function facturas()
    {
        return $this->hasMany(
            Factura::class,
            'id_cliente',   // FK en facturas (ajusta con FacCol si ya lo tienes)
            Col::PK
        );
        // p.ej:
        // return $this->hasMany(Factura::class, FacCol::CLIENTE_ID, Col::PK);
    }

    /**
     * Scope: clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where(Col::ESTADO, Col::ESTADO_ACTIVO);
    }

    /**
     * Lógica de dominio:
     *  - Si el RUC/Cédula ya existe → devuelve ese cliente SIN modificarlo.
     *  - Si no existe → crea un cliente nuevo con los datos recibidos.
     *
     * @param  array $clienteData  Datos del formulario, con keys de ClienteColumns
     * @return \App\Models\Cliente
     */
    public static function obtenerORegistrarPorIdentificacion(array $clienteData): self
    {
        $rucCed = $clienteData[Col::RUC_CED] ?? null;

        if (!$rucCed) {
            // aquí puedes lanzar una excepción propia si quieres,
            // por ahora tiramos una genérica
            throw new \InvalidArgumentException('El campo ' . Col::RUC_CED . ' es obligatorio.');
        }

        // 1) ¿Ya existe un cliente con ese RUC/Cédula?
        $clienteExistente = static::where(Col::RUC_CED, $rucCed)->first();

        if ($clienteExistente) {
            // 🔒 Regla: si existe, NO se actualiza nada.
            // Se devuelve tal cual está en base de datos.
            return $clienteExistente;
        }

        // 2) No existe → creamos un nuevo cliente con los datos del formulario
        $cliente = new static();

        $cliente->{Col::NOMBRE}    = $clienteData[Col::NOMBRE]    ?? '';
        $cliente->{Col::RUC_CED}   = $rucCed;
        $cliente->{Col::MAIL}      = $clienteData[Col::MAIL]      ?? null;
        $cliente->{Col::TELEFONO}  = $clienteData[Col::TELEFONO]  ?? null;
        $cliente->{Col::DIRECCION} = $clienteData[Col::DIRECCION] ?? null;
        $cliente->{Col::CIUDAD_ID} = $clienteData[Col::CIUDAD_ID] ?? null;
        $cliente->{Col::ESTADO}    = $clienteData[Col::ESTADO]    ?? Col::ESTADO_ACTIVO;

        // OJO: no seteamos Col::PK para que lo genere el trigger.
        $cliente->save(); // el trigger pone id_cliente

        return $cliente;
    }
}
