<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\UsuarioColumns as UsuCol;
use App\Constants\ClienteColumns as CliCol;
use App\Models\Cliente;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ruc = $this->input(CliCol::RUC_CED);

        // ¿Ya existe un cliente con esta cédula/RUC?
        $clienteExiste = false;
        if ($ruc) {
            $clienteExiste = Cliente::where(CliCol::RUC_CED, $ruc)->exists();
        }

        // Si NO existe cliente → es cliente nuevo → ciudad obligatoria
        // Si SÍ existe cliente → ciudad opcional (no la pedimos en este form)
        $esNuevoCliente = !$clienteExiste;

        return [
            // ================== CLIENTE ==================
            CliCol::NOMBRE    => [
                'required',
                'string',
                'max:40',
                'regex:/^[\pL\s\.]+$/u', // letras, espacios y puntos
            ],

            CliCol::RUC_CED   => [
                'required',
                'string',
                // 10 o 13 dígitos numéricos
                'regex:/^(?:\d{10}|\d{13})$/',
            ],

            CliCol::MAIL      => 'nullable|email|max:60',
            CliCol::TELEFONO  => 'nullable|string|max:10',
            CliCol::DIRECCION => 'nullable|string|max:60',

            CliCol::CIUDAD_ID => [
                $esNuevoCliente ? 'required' : 'nullable',
                'string',
                'size:3',
                'exists:ciudades,id_ciudad',
            ],

            // ================== USUARIO ==================
            UsuCol::USERNAME  => 'required|string|max:50|unique:' . UsuCol::TABLE . ',' . UsuCol::USERNAME,
            UsuCol::PASSWORD  => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            // CLIENTE
            CliCol::NOMBRE . '.required'   => 'El nombre o razón social es obligatorio.',
            CliCol::NOMBRE . '.string'     => 'El nombre o razón social no es válido.',
            CliCol::NOMBRE . '.max'        => 'El nombre o razón social no debe exceder 40 caracteres.',
            CliCol::NOMBRE . '.regex'      => 'El nombre solo puede contener letras, espacios y puntos.',

            CliCol::RUC_CED . '.required'  => 'La cédula/RUC es obligatoria.',
            CliCol::RUC_CED . '.string'    => 'La cédula/RUC no es válida.',
            CliCol::RUC_CED . '.regex'     => 'La cédula/RUC debe tener exactamente 10 o 13 dígitos numéricos.',

            CliCol::MAIL . '.email'        => 'El correo electrónico no tiene un formato válido.',
            CliCol::MAIL . '.max'          => 'El correo electrónico no debe exceder 60 caracteres.',

            CliCol::TELEFONO . '.max'      => 'El teléfono no debe exceder 10 caracteres.',

            CliCol::DIRECCION . '.max'     => 'La dirección no debe exceder 60 caracteres.',

            CliCol::CIUDAD_ID . '.required' => 'La ciudad es obligatoria para nuevos clientes.',
            CliCol::CIUDAD_ID . '.size'     => 'La ciudad debe tener exactamente 3 caracteres.',
            CliCol::CIUDAD_ID . '.exists'   => 'La ciudad seleccionada no es válida.',

            // USUARIO
            UsuCol::USERNAME . '.required'  => 'El nombre de usuario es obligatorio.',
            UsuCol::USERNAME . '.max'       => 'El nombre de usuario no debe exceder 50 caracteres.',
            UsuCol::USERNAME . '.unique'    => 'Este nombre de usuario ya está en uso.',

            UsuCol::PASSWORD . '.required'  => 'La contraseña es obligatoria.',
            UsuCol::PASSWORD . '.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            UsuCol::PASSWORD . '.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
