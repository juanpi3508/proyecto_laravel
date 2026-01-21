<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Constants\ClienteColumns as CliCol;

class ClienteLookupController extends Controller
{
    public function buscarPorRuc(Request $request)
    {
        $ruc = trim($request->get('ruc'));

        $cliente = Cliente::where(CliCol::RUC_CED, $ruc)->first();

        // Escenario 1: NO existe cliente
        if (!$cliente) {
            return response()->json([
                'status' => 'no_cliente',
            ]);
        }

        // Estado del cliente
        $estado = $cliente->{CliCol::ESTADO} ?? 'ACT';

        // Escenario 3 (INA): cliente inactivo
        if ($estado === 'INA') {
            return response()->json([
                'status'  => 'cliente_inactivo',
                'cliente' => [
                    'id_cliente' => $cliente->id_cliente,
                    'cli_nombre' => $cliente->{CliCol::NOMBRE},
                ],
            ]);
        }

        // Cliente ACTIVO → ver si tiene usuario
        $tieneUsuario = Usuario::where('id_cliente', $cliente->id_cliente)->exists();

        if ($tieneUsuario) {
            // Escenario 3: ACT y con usuario
            return response()->json([
                'status' => 'cliente_con_usuario',
            ]);
        }

        // Escenario 2: ACT y SIN usuario
        return response()->json([
            'status'  => 'cliente_sin_usuario',
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'cli_nombre' => $cliente->{CliCol::NOMBRE},
            ],
        ]);
    }
}
