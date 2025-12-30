<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    public $incrementing = false;
    protected $keyType = 'string';

    // Tu tabla no tiene created_at / updated_at
    public $timestamps = false;

    /**
     * Campos que TE IMPORTAN (facturación / contacto).
     * Con esto podrás crear clientes desde Tinker si quieres.
     */
    protected $fillable = [
        'id_cliente',
        'cli_nombre',
        'cli_ruc_ced',
        'cli_telefono',
        'cli_mail',
        'cli_direccion',
        'id_ciudad',
        'estado_cli',
    ];

    /**
     * Relación: un cliente puede tener muchos usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_cliente', 'id_cliente');
    }

    /**
     * (Opcional) Relación con facturas si luego haces el modelo Factura
     * public function facturas()
     * {
     *     return $this->hasMany(Factura::class, 'id_cliente', 'id_cliente');
     * }
     */
}
