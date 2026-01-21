<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Ciudad;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            ->with('login_success_message', '¡Bienvenido de nuevo a KoKo Market!');
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

        $ciudades = Ciudad::orderBy('ciu_descripcion')->get();

        return view('auth.register', compact('ciudades'));
    }

    public function register(RegisterRequest $request)
    {
        // YA viene validado por RegisterRequest
        $ruc       = $request->input(CliCol::RUC_CED);
        $clienteId = $request->input('cliente_id');

        // Si viene cliente_id desde el front (escenario cliente existente sin usuario), lo usamos;
        // si no, buscamos por RUC/Cédula
        if ($clienteId) {
            $cliente = Cliente::find($clienteId);
        } else {
            $cliente = Cliente::where(CliCol::RUC_CED, $ruc)->first();
        }

        // ---------- ESCENARIO 3: Validaciones de seguridad en backend ----------

        if ($cliente) {
            // Estado (ACT / INA / etc.)
            $estado = $cliente->{CliCol::ESTADO} ?? 'ACT';

            // 3.a) Cliente INA → no permitimos registro
            if ($estado === 'INA') {
                return back()
                    ->withErrors([
                        CliCol::RUC_CED => 'Tu cuenta de cliente está inactiva. Por favor contáctate con nosotros.',
                    ])
                    ->withInput();
            }

            // 3.b) Cliente ACT pero YA tiene usuario → no permitimos otro usuario
            $yaTieneUsuario = Usuario::where('id_cliente', $cliente->id_cliente)->exists();

            if ($yaTieneUsuario) {
                return back()
                    ->withErrors([
                        CliCol::RUC_CED => 'Esta cédula/RUC ya tiene un usuario asociado. Por favor inicia sesión o usa "Olvidé mi contraseña".',
                    ])
                    ->withInput();
            }

            // Si llegó hasta aquí → Cliente ACT y SIN usuario (Escenario 2)
        }

        // ---------- ESCENARIO 1 y 2: Crear cliente (si hace falta) + usuario ----------

        $usuario = null;

        DB::transaction(function () use ($request, $ruc, &$cliente, &$usuario) {

            // ESCENARIO 1: NO existe cliente → lo creamos con todos los datos del formulario
            if (!$cliente) {
                Cliente::create([
                    CliCol::RUC_CED    => $ruc,
                    CliCol::NOMBRE     => $request->input(CliCol::NOMBRE),
                    CliCol::MAIL       => $request->input(CliCol::MAIL),
                    CliCol::TELEFONO   => $request->input(CliCol::TELEFONO),
                    CliCol::DIRECCION  => $request->input(CliCol::DIRECCION),
                    CliCol::CIUDAD_ID  => $request->input(CliCol::CIUDAD_ID),
                    CliCol::ESTADO     => 'ACT',
                ]);
            } else {
                // ESCENARIO 2: Cliente ACT sin usuario.
                // No modificamos datos del cliente para no alterar su ficha.
            }

            // 🔁 MUY IMPORTANTE:
            // Volvemos a leer el cliente DESDE LA BD para asegurar que tenga id_cliente
            // (el trigger/SP ya corrió y generó el ID).
            $cliente = Cliente::where(CliCol::RUC_CED, $ruc)->first();

            if (!$cliente || empty($cliente->id_cliente)) {
                throw new \RuntimeException('No se pudo obtener el ID de cliente desde la base de datos.');
            }

            // Crear usuario asociado al cliente (nuevo o existente)
            $usuario = new Usuario();

            $usuario->{UsuCol::PK}       = Str::upper(Str::random(10)); // CHAR(10)
            $usuario->{UsuCol::USERNAME} = $request->input(UsuCol::USERNAME);
            $usuario->{UsuCol::PASSWORD} = Hash::make($request->input(UsuCol::PASSWORD));
            $usuario->{'id_cliente'}     = $cliente->id_cliente;
            $usuario->{UsuCol::ESTADO}   = UsuCol::ESTADO_ACTIVO;
            $usuario->{UsuCol::INTENTOS} = 0;

            $usuario->save();
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        // 3. Redirigir al carrito con el MISMO flag que usamos en login
        return redirect()
            ->route('carrito.index')
            ->with('login_success', true)
            ->with('login_success_message', '¡Tu cuenta se creó con éxito, bienvenido a KoKo Market!');
    }
}
