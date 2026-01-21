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

    public function getAuthPassword()
    {
        return $this->{Col::PASSWORD};
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

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

    /**
     * Lógica de dominio:
     *  - Obtiene o crea el cliente por RUC/Cédula.
     *  - Crea el usuario asociado a ese cliente.
     *
     * IMPORTANTE:
     *  - Si el cliente ya existe, NO se modifican sus datos.
     *
     * @param  array $clienteData  datos de cliente (keys ClienteColumns)
     * @param  array $usuarioData  datos de usuario (keys UsuarioColumns)
     * @return \App\Models\Usuario
     */
    public static function registrarConCliente(array $clienteData, array $usuarioData): self
    {
        return DB::transaction(function () use ($clienteData, $usuarioData) {

            // 1) Cliente: o bien existente (por RUC/Cédula) o uno nuevo
            $cliente = Cliente::obtenerORegistrarPorIdentificacion($clienteData);

            // 2) Crear usuario asociado
            $usuario = new static();

            $usuario->{Col::PK}       = Str::upper(Str::random(10)); // CHAR(10)
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
