<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Si ya está logueado, lo mandamos al catálogo
        if (Auth::check()) {
            return redirect()->route('catalogo.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usu_usuario' => 'required|string',
            'usu_contrasena' => 'required|string',
        ]);

        $user = Usuario::where('usu_usuario', $request->usu_usuario)->first();

        $errorMsg = 'Credenciales inválidas o usuario inactivo.';

        if (!$user || $user->estado_usu !== 'ACT') {
            return back()->withErrors(['usu_usuario' => $errorMsg])->onlyInput('usu_usuario');
        }

        if (!Hash::check($request->usu_contrasena, $user->usu_contrasena)) {

            $user->usu_intentos_fallidos = (int) $user->usu_intentos_fallidos + 1;

            if ($user->usu_intentos_fallidos >= 5) {
                $user->estado_usu = 'BLQ';
            }

            $user->save();

            return back()->withErrors(['usu_usuario' => $errorMsg])->onlyInput('usu_usuario');
        }

        // OK: reset intentos + último acceso
        $user->usu_intentos_fallidos = 0;
        $user->usu_ultimo_acceso = now();
        $user->save();

        Auth::login($user->load('cliente'));
        $request->session()->regenerate();

        // ✅ IMPORTANTE: vuelve al checkout si venía de ahí
        return redirect()->intended(route('catalogo.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
