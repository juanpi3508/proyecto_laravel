<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalogo', [ProductController::class, 'catalogo'])
    ->name('catalogo.index');

Route::get('/productos/{token}', [ProductController::class, 'show'])
    ->where('token', '.*')
    ->name('productos.show');

Route::prefix('carrito')->name('carrito.')->group(function () {
    Route::get('/', [CarritoController::class, 'index'])->name('index');
    Route::post('/', [CarritoController::class, 'store'])->name('store');
    Route::delete('/vaciar', [CarritoController::class, 'clear'])->name('clear');
    Route::put('/{idProducto}', [CarritoController::class, 'update'])->name('update');
    Route::delete('/{idProducto}', [CarritoController::class, 'destroy'])->name('destroy');
});

Route::post('/factura/generar', [FacturaController::class, 'generarFactura'])
    ->name('factura.generar');

Route::get('/factura/{id}/confirmar', [FacturaController::class, 'confirmar'])
    ->name('factura.confirmar');

Route::post('/factura/{id}/aprobar', [FacturaController::class, 'aprobar'])
    ->name('factura.aprobar');

Route::get('/factura/{id}', [FacturaController::class, 'show'])
    ->name('factura.show');

Route::get('/historial-compras', [FacturaController::class, 'listarFacturas'])
    ->name('facturas.historial');

Route::get('/factura/{id}/popup', [FacturaController::class, 'detallePopup'])
    ->name('factura.popup');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
