<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 👇 Usa el que corresponda a tu versión de Bootstrap
        Paginator::useBootstrapFive();   // si usas Bootstrap 5
        // Paginator::useBootstrapFour(); // si usas Bootstrap 4

        // View Composer para inyectar contador del carrito al header
        View::composer('layouts.header', function ($view) {
            $view->with('cartCount', count(session('carrito', [])));
        });
    }
}
