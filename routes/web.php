<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home.index');
})->name('home');

Route::get('/catalogo', [ProductController::class, 'catalogo'])
    ->name('catalogo.index');

Route::get('/productos/{token}', [ProductController::class, 'show'])
    ->where('token', '.*')
    ->name('productos.show');

/*
|--------------------------------------------------------------------------
| Rutas del Carrito
|--------------------------------------------------------------------------
*/

Route::prefix('carrito')->name('carrito.')->group(function () {
    Route::get('/', [CarritoController::class, 'index'])->name('index');
    Route::post('/', [CarritoController::class, 'store'])->name('store');
    Route::put('/{idProducto}', [CarritoController::class, 'update'])->name('update');
    Route::delete('/{idProducto}', [CarritoController::class, 'destroy'])->name('destroy');
    Route::delete('/vaciar', [CarritoController::class, 'clear'])->name('clear');
});

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
