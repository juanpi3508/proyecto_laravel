<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function catalogo(Request $request)
    {
        // Parámetros de filtros
        $q    = $request->input('q');
        $cat  = $request->input('cat');
        $sort = $request->input('sort', 'relevance');

        // Query base del catálogo
        $query = Product::with('categoria')
            ->where('estado_prod', 'ACT');

        if (!empty($q)) {
            $query->where('pro_descripcion', 'LIKE', '%' . $q . '%');
        }

        if (!empty($cat)) {
            $query->where('id_categoria', $cat);
        }

        switch ($sort) {
            case 'price-asc':
                $query->orderBy('pro_precio_venta', 'asc');
                break;

            case 'price-desc':
                $query->orderBy('pro_precio_venta', 'desc');
                break;

            case 'name-asc':
                $query->orderBy('pro_descripcion', 'asc');
                break;

            case 'name-desc':
                $query->orderBy('pro_descripcion', 'desc');
                break;

            default:
                // Relevancia / recientes
                $query->orderBy('id_producto', 'desc');
                break;
        }

        // Retorno a la vista
        return view('catalogo.index', [
            'productos'  => $query->get(),
            'categorias' => Category::orderBy('cat_descripcion')->get(),
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort
        ]);
    }

    public function show($id)
    {
        $producto = Product::with('categoria')
            ->where('id_producto', $id)
            ->where('estado_prod', 'A')
            ->firstOrFail();

        $relacionados = Product::where('estado_prod', 'A')
            ->where('id_categoria', $producto->id_categoria)
            ->where('id_producto', '!=', $producto->id_producto)
            ->limit(4)
            ->get();

        return view('productos.show', compact('producto', 'relacionados'));
    }
}
