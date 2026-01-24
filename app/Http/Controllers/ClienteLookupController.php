<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteLookupController extends Controller
{
    public function buscarPorRuc(Request $request)
    {
        $ruc = trim($request->get('ruc'));

        // Delegar toda la lógica al modelo
        return response()->json(
            Cliente::escenarioRegistro($ruc)
        );
    }
}
