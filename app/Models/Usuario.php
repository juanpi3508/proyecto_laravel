<?php

namespace App\Models;

use App\Constants\UsuarioColumns as Col;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
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
     * - valida estado
     * - registra intentos fallidos / bloqueo
     * - resetea intentos y guarda ultimo acceso si ok
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
}
