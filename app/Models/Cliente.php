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

    protected $casts = [
        Col::PK        => 'string',
        Col::CIUDAD_ID => 'string',
    ];

    // ==================== RELACIONES ====================

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, Col::CIUDAD_ID, 'id_ciudad');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_cliente', Col::PK);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'id_cliente', Col::PK);
    }

    // ==================== SCOPES ====================

    public function scopeActivos($query)
    {
        return $query->where(Col::ESTADO, Col::ESTADO_ACTIVO);
    }

    // ==================== MÉTODOS DE BÚSQUEDA ====================

    public static function buscarPorRucCed(string $ruc): ?self
    {
        return static::where(Col::RUC_CED, $ruc)->first();
    }

    // ==================== LÓGICA DE NEGOCIO ====================

    /**
     * Verifica si el cliente ya tiene un usuario asociado.
     */
    public function tieneUsuario(): bool
    {
        return Usuario::where('id_cliente', $this->{Col::PK})->exists();
    }

    /**
     * Verifica si el cliente está activo.
     */
    public function estaActivo(): bool
    {
        return ($this->{Col::ESTADO} ?? 'ACT') === 'ACT';
    }

    /**
     * Verifica si el cliente está inactivo.
     */
    public function estaInactivo(): bool
    {
        return ($this->{Col::ESTADO} ?? 'ACT') === 'INA';
    }

    /**
     * Valida si el cliente puede registrar un nuevo usuario.
     * Lanza excepción con mensaje del config si no es válido.
     *
     * @throws \InvalidArgumentException
     */
    public function validarParaRegistro(): void
    {
        $errors = config('register_messages.errors');

        if ($this->estaInactivo()) {
            throw new \InvalidArgumentException($errors['cliente_inactivo']);
        }

        if ($this->tieneUsuario()) {
            throw new \InvalidArgumentException($errors['cliente_con_usuario']);
        }
    }

    /**
     * Determina el escenario de registro para un RUC/Cédula.
     * Usado por ClienteLookupController para devolver JSON.
     *
     * Retorna array con:
     * - status: 'no_cliente' | 'cliente_sin_usuario' | 'cliente_con_usuario' | 'cliente_inactivo'
     * - cliente: datos básicos del cliente (si aplica)
     */
    public static function escenarioRegistro(string $ruc): array
    {
        $cliente = static::buscarPorRucCed($ruc);

        // Escenario 1: NO existe cliente
        if (!$cliente) {
            return ['status' => 'no_cliente'];
        }

        // Escenario 3a: Cliente inactivo
        if ($cliente->estaInactivo()) {
            return [
                'status'  => 'cliente_inactivo',
                'cliente' => [
                    'id_cliente' => $cliente->{Col::PK},
                    'cli_nombre' => $cliente->{Col::NOMBRE},
                ],
            ];
        }

        // Cliente activo → verificar si tiene usuario
        if ($cliente->tieneUsuario()) {
            // Escenario 3b: Ya tiene usuario
            return ['status' => 'cliente_con_usuario'];
        }

        // Escenario 2: Cliente activo sin usuario
        return [
            'status'  => 'cliente_sin_usuario',
            'cliente' => [
                'id_cliente' => $cliente->{Col::PK},
                'cli_nombre' => $cliente->{Col::NOMBRE},
            ],
        ];
    }

    /**
     * Obtiene o registra un cliente por identificación (RUC/Cédula).
     * Si ya existe, lo retorna SIN modificar.
     * Si no existe, lo crea con los datos proporcionados.
     */
    public static function obtenerORegistrarPorIdentificacion(array $clienteData): self
    {
        $rucCed = $clienteData[Col::RUC_CED] ?? null;

        if (!$rucCed) {
            throw new \InvalidArgumentException(config('auth_messages.errors.ruc_required'));
        }

        // ¿Ya existe?
        $clienteExistente = static::buscarPorRucCed($rucCed);

        if ($clienteExistente) {
            return $clienteExistente;
        }

        // No existe → crear nuevo
        $cliente = new static();

        $cliente->{Col::NOMBRE}    = $clienteData[Col::NOMBRE]    ?? '';
        $cliente->{Col::RUC_CED}   = $rucCed;
        $cliente->{Col::MAIL}      = $clienteData[Col::MAIL]      ?? null;
        $cliente->{Col::TELEFONO}  = $clienteData[Col::TELEFONO]  ?? null;
        $cliente->{Col::DIRECCION} = $clienteData[Col::DIRECCION] ?? null;
        $cliente->{Col::CIUDAD_ID} = $clienteData[Col::CIUDAD_ID] ?? null;
        $cliente->{Col::ESTADO}    = $clienteData[Col::ESTADO]    ?? Col::ESTADO_ACTIVO;

        $cliente->save(); // El trigger genera id_cliente

        // Refrescar desde BD para obtener el ID generado
        return static::buscarPorRucCed($rucCed);
    }
}
