<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // Página de detalle del producto
    public function show($id)
    {
        // Trae el producto por su PK
        $producto = Product::with('categoria')
            ->where('id_producto', $id)
            ->firstOrFail();

        // Productos relacionados: misma categoría
        $relacionados = Product::where('id_categoria', $producto->id_categoria)
            ->where('id_producto', '!=', $producto->id_producto)
            ->take(4)
            ->get();

        return view('productos.show', compact('producto', 'relacionados'));
    }
}
