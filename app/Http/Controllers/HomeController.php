<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $stats = config('koko.stats');

        $masVendidos = Product::masVendidos(5);

        return view('home.index', compact('stats', 'masVendidos'));
    }
}
