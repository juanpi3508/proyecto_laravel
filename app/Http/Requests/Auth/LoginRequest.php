<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\UsuarioColumns as UsuCol;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            UsuCol::USERNAME => 'required|string',
            UsuCol::PASSWORD => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            UsuCol::USERNAME . '.required' => config('auth_messages.validation.username_required'),
            UsuCol::PASSWORD . '.required' => config('auth_messages.validation.password_required'),
        ];
    }
}
