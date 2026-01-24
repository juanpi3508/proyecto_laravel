<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Ciudad;
use Illuminate\Support\Facades\Auth;
use App\Constants\UsuarioColumns as UsuCol;
use App\Constants\ClienteColumns as CliCol;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('carrito.index');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $user = Usuario::autenticar(
            $request->input(UsuCol::USERNAME),
            $request->input(UsuCol::PASSWORD)
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('carrito.index'))
            ->with('login_success', true)
            ->with('login_success_message', config('auth_messages.success.login'));
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('carrito.index');
        }

        $ciudades = Ciudad::paraSelector();

        // Determinar si mostrar campos extra (errores de validación o valores de formulario previo)
        $mostrarCamposExtra = old(CliCol::CIUDAD_ID)
            || old(CliCol::MAIL)
            || old(CliCol::TELEFONO)
            || old(CliCol::DIRECCION)
            || session('errors')?->has(CliCol::CIUDAD_ID)
            || session('errors')?->has(CliCol::MAIL)
            || session('errors')?->has(CliCol::TELEFONO)
            || session('errors')?->has(CliCol::DIRECCION);

        return view('auth.register', compact('ciudades', 'mostrarCamposExtra'));
    }

    public function register(RegisterRequest $request)
    {
        // Preparar datos del cliente
        $clienteData = [
            CliCol::RUC_CED   => $request->input(CliCol::RUC_CED),
            CliCol::NOMBRE    => $request->input(CliCol::NOMBRE),
            CliCol::MAIL      => $request->input(CliCol::MAIL),
            CliCol::TELEFONO  => $request->input(CliCol::TELEFONO),
            CliCol::DIRECCION => $request->input(CliCol::DIRECCION),
            CliCol::CIUDAD_ID => $request->input(CliCol::CIUDAD_ID),
        ];

        // Preparar datos del usuario
        $usuarioData = [
            UsuCol::USERNAME => $request->input(UsuCol::USERNAME),
            UsuCol::PASSWORD => $request->input(UsuCol::PASSWORD),
        ];

        // Cliente ID existente (escenario 2: cliente sin usuario)
        $clienteIdExistente = $request->input('cliente_id');

        try {
            // Delegar toda la lógica al modelo
            $usuario = Usuario::registrarConCliente(
                $clienteData,
                $usuarioData,
                $clienteIdExistente
            );

            Auth::login($usuario);
            $request->session()->regenerate();

            return redirect()
                ->route('carrito.index')
                ->with('login_success', true)
                ->with('login_success_message', config('auth_messages.success.register'));

        } catch (\InvalidArgumentException $e) {
            // Errores de validación de dominio (cliente inactivo, ya tiene usuario)
            return back()
                ->withErrors([CliCol::RUC_CED => $e->getMessage()])
                ->withInput();

        } catch (\Throwable $e) {
            // Otros errores
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
