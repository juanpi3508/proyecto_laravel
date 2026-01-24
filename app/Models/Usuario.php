<?php

namespace App\Models;

use App\Constants\UsuarioColumns as Col;
use App\Constants\ClienteColumns as CliCol;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Usuario extends Authenticatable
{
    protected $table = Col::TABLE;
    protected $primaryKey = Col::PK;

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $hidden = [
        Col::PASSWORD,
    ];

    // ==================== RELACIONES ====================

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    // ==================== AUTH ====================

    public function getAuthPassword()
    {
        return $this->{Col::PASSWORD};
    }

    // ==================== LÓGICA DE AUTENTICACIÓN ====================

    /**
     * Autentica un usuario por username + password.
     */
    public static function autenticar(string $username, string $password): self
    {
        $user = static::where(Col::USERNAME, $username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                Col::USERNAME => config('auth_messages.errors.invalid_credentials'),
            ]);
        }

        if ($user->{Col::ESTADO} !== Col::ESTADO_ACTIVO) {
            throw ValidationException::withMessages([
                Col::USERNAME => config('auth_messages.errors.inactive_user'),
            ]);
        }

        if (!Hash::check($password, $user->{Col::PASSWORD})) {
            $user->registrarIntentoFallido();

            throw ValidationException::withMessages([
                Col::USERNAME => config('auth_messages.errors.invalid_credentials'),
            ]);
        }

        $user->registrarLoginExitoso();

        return $user->load('cliente');
    }

    private function registrarIntentoFallido(): void
    {
        $this->{Col::INTENTOS} = (int) ($this->{Col::INTENTOS} ?? 0) + 1;

        if ($this->{Col::INTENTOS} >= 5) {
            $this->{Col::ESTADO} = Col::ESTADO_BLOQUEADO;
        }

        $this->save();
    }

    private function registrarLoginExitoso(): void
    {
        $this->{Col::INTENTOS} = 0;
        $this->{Col::ULTIMO_ACCESO} = now();
        $this->save();
    }

    // ==================== LÓGICA DE REGISTRO ====================

    /**
     * Registra un nuevo usuario junto con su cliente.
     *
     * Escenarios:
     * 1. Cliente nuevo: crea cliente + usuario
     * 2. Cliente existente sin usuario: usa cliente existente + crea usuario
     *
     * Validaciones de dominio:
     * - Si el cliente está inactivo → lanza excepción
     * - Si el cliente ya tiene usuario → lanza excepción
     *
     * @param  array $clienteData  Datos del cliente (keys de ClienteColumns)
     * @param  array $usuarioData  Datos del usuario (keys de UsuarioColumns)
     * @param  string|null $clienteIdExistente  ID de cliente existente (escenario 2)
     * @return self Usuario creado con su cliente cargado
     * @throws \InvalidArgumentException Si el cliente no puede registrar usuario
     */
    public static function registrarConCliente(
        array $clienteData,
        array $usuarioData,
        ?string $clienteIdExistente = null
    ): self {
        return DB::transaction(function () use ($clienteData, $usuarioData, $clienteIdExistente) {

            // 1) Obtener cliente (existente o nuevo)
            if ($clienteIdExistente) {
                // Escenario 2: Cliente existente proporcionado por el front
                $cliente = Cliente::find($clienteIdExistente);

                if (!$cliente) {
                    // Fallback: buscar por RUC
                    $cliente = Cliente::buscarPorRucCed($clienteData[CliCol::RUC_CED] ?? '');
                }
            } else {
                // Buscar por RUC/Cédula
                $cliente = Cliente::buscarPorRucCed($clienteData[CliCol::RUC_CED] ?? '');
            }

            // 2) Validar cliente existente para registro
            if ($cliente) {
                $cliente->validarParaRegistro(); // Lanza excepción si no es válido
            } else {
                // 3) Crear cliente nuevo
                $cliente = Cliente::obtenerORegistrarPorIdentificacion($clienteData);
            }

            // 4) Refrescar cliente desde BD para asegurar que tiene ID
            $cliente = Cliente::buscarPorRucCed($clienteData[CliCol::RUC_CED] ?? $cliente->{CliCol::RUC_CED});

            if (!$cliente || empty($cliente->{CliCol::PK})) {
                throw new \RuntimeException(config('auth_messages.errors.cliente_id_error'));
            }

            // 5) Crear usuario asociado
            $usuario = new static();

            $usuario->{Col::PK}       = Str::upper(Str::random(10));
            $usuario->{Col::USERNAME} = $usuarioData[Col::USERNAME];
            $usuario->{Col::PASSWORD} = Hash::make($usuarioData[Col::PASSWORD]);
            $usuario->{'id_cliente'}  = $cliente->{CliCol::PK};
            $usuario->{Col::ESTADO}   = Col::ESTADO_ACTIVO;
            $usuario->{Col::INTENTOS} = 0;

            $usuario->save();

            return $usuario->load('cliente');
        });
    }
}
