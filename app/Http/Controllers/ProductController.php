<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function catalogo(Request $request)
    {
        $q    = trim((string) $request->input('q', ''));
        $cat  = $request->input('cat');
        $sort = $request->input('sort', 'relevance');

        // Opcional: validar sort para evitar valores raros
        $allowedSorts = [
            'relevance',
            'price-asc',
            'price-desc',
            'name-asc',
            'name-desc',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'relevance';
        }

        $productos = Product::catalogo($q ?: null, $cat ?: null, $sort);

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
