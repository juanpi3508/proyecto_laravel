<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('home.index');
})->name('home');


Route::get('/catalogo', [ProductController::class, 'catalogo'])
    ->name('catalogo.index');

Route::get('/productos/{token}', [ProductController::class, 'show'])
    ->where('token', '.*')
    ->name('productos.show');

