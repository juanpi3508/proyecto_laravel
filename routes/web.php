<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('home.index'); // ← tu nueva view
})->name('home');

Route::get('/productos', [ProductController::class, 'index'])
    ->name('productos.index');

Route::get('/productos/{id}', [ProductController::class, 'show'])
    ->name('productos.show');
