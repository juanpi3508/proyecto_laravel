<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function catalogo(Request $request)
    {
        $q    = $request->input('q');
        $cat  = $request->input('cat');
        $sort = $request->input('sort', 'relevance');

        $productos = Product::catalogo($q, $cat, $sort);

        return view('catalogo.index', [
            'productos'  => $productos,
            'categorias' => Category::paraCatalogo(),
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort,
        ]);
    }

    public function show(string $token)
    {
        $producto = Product::findByTokenOrFail($token);

        $relacionados = Product::relacionados($producto, 4);

        return view('productos.show', compact('producto', 'relacionados'));
    }
}
