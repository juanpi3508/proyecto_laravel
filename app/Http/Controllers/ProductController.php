<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Catálogo de productos (listado + filtros + paginación).
     */
    public function catalogo(Request $request)
    {
        // Obtenemos solo los filtros necesarios
        $filters = $request->only(['q', 'cat', 'sort']);

        // Toda la lógica de búsqueda / orden / paginación vive en el modelo
        $productos = Product::catalogo($filters);

        return view('catalogo.index', [
            'productos'  => $productos,
            'categorias' => Category::paraCatalogo(),
            'q'          => $filters['q']   ?? '',
            'cat'        => $filters['cat'] ?? null,
            'sort'       => $filters['sort'] ?? 'relevance',
        ]);
    }

    /**
     * Detalle de un producto por token público.
     */
    public function show(string $token)
    {
        // Lógica de búsqueda por token en el modelo
        $producto = Product::findByTokenOrFail($token);

        // Lógica para traer productos relacionados en el modelo
        $relacionados = Product::relacionados($producto, 4);

        return view('productos.show', compact('producto', 'relacionados'));
    }
}
