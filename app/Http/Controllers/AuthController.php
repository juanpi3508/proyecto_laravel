<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constants\UsuarioColumns as Col;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('carrito.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                Col::USERNAME => 'required|string',
                Col::PASSWORD => 'required|string',
            ],
            [
                Col::USERNAME . '.required' => config('auth_messages.validation.username_required'),
                Col::PASSWORD . '.required' => config('auth_messages.validation.password_required'),
            ]
        );

        $user = Usuario::autenticar(
            $request->input(Col::USERNAME),
            $request->input(Col::PASSWORD)
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('carrito.index'));
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
