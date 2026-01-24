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

        // Si NO existe cliente → es cliente nuevo → todos los campos obligatorios
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
                function ($attribute, $value, $fail) {
                    $this->validarRucCedula($value, $fail);
                },
            ],

            CliCol::MAIL      => [
                $esNuevoCliente ? 'required' : 'nullable',
                'email',
                'max:60',
            ],

            CliCol::TELEFONO  => [
                $esNuevoCliente ? 'required' : 'nullable',
                'string',
                'max:10',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->validarCelular($value)) {
                        $fail(config('register_messages.errors.celular_formato'));
                    }
                },
            ],

            CliCol::DIRECCION => [
                $esNuevoCliente ? 'required' : 'nullable',
                'string',
                'max:60',
            ],

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

    /**
     * Valida cédula (10 dígitos) o RUC (13 dígitos terminando en 001)
     */
    private function validarRucCedula(string $value, callable $fail): void
    {
        // Solo números
        if (!preg_match('/^\d+$/', $value)) {
            $fail(config('register_messages.errors.ruc_solo_numeros'));
            return;
        }

        $length = strlen($value);

        // Cédula: exactamente 10 dígitos
        if ($length === 10) {
            return; // Válido
        }

        // RUC: exactamente 13 dígitos y terminar en 001
        if ($length === 13) {
            if (!str_ends_with($value, '001')) {
                $fail(config('register_messages.errors.ruc_formato_ruc'));
            }
            return;
        }

        // Longitud inválida
        $fail(config('register_messages.errors.ruc_longitud'));
    }

    /**
     * Valida que el celular empiece con 09 y tenga 10 dígitos
     */
    private function validarCelular(string $value): bool
    {
        // Debe empezar con 09 y tener exactamente 10 dígitos
        return preg_match('/^09\d{8}$/', $value) === 1;
    }

    public function messages(): array
    {
        $errors = config('register_messages.errors');

        return [
            // CLIENTE
            CliCol::NOMBRE . '.required'   => $errors['nombre_vacio'],
            CliCol::NOMBRE . '.string'     => $errors['nombre_vacio'],
            CliCol::NOMBRE . '.max'        => $errors['nombre_max'],
            CliCol::NOMBRE . '.regex'      => $errors['nombre_formato'],

            CliCol::RUC_CED . '.required'  => $errors['ruc_vacio'],
            CliCol::RUC_CED . '.string'    => $errors['ruc_vacio'],

            CliCol::MAIL . '.required'     => $errors['email_vacio'],
            CliCol::MAIL . '.email'        => $errors['email_formato'],
            CliCol::MAIL . '.max'          => $errors['email_max'],

            CliCol::TELEFONO . '.required' => $errors['celular_vacio'],
            CliCol::TELEFONO . '.max'      => $errors['celular_max'],

            CliCol::DIRECCION . '.required' => $errors['direccion_vacio'],
            CliCol::DIRECCION . '.max'      => $errors['direccion_max'],

            CliCol::CIUDAD_ID . '.required' => $errors['ciudad_requerida'],
            CliCol::CIUDAD_ID . '.size'     => $errors['ciudad_invalida'],
            CliCol::CIUDAD_ID . '.exists'   => $errors['ciudad_invalida'],

            // USUARIO
            UsuCol::USERNAME . '.required'  => $errors['usuario_vacio'],
            UsuCol::USERNAME . '.max'       => $errors['usuario_max'],
            UsuCol::USERNAME . '.unique'    => $errors['usuario_en_uso'],

            UsuCol::PASSWORD . '.required'  => $errors['password_vacio'],
            UsuCol::PASSWORD . '.min'       => $errors['password_min'],
            UsuCol::PASSWORD . '.confirmed' => $errors['password_confirmar'],
        ];
    }
}
