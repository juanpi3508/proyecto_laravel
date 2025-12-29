<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('home.index');
})->name('home');


Route::get('/catalogo', [ProductController::class, 'catalogo'])
    ->name('catalogo.index');

Route::get('/productos', [ProductController::class, 'index'])
    ->name('productos.index');

Route::get('/productos/{id}', [ProductController::class, 'show'])
    ->name('productos.show');
