<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\OrdersController;

Route::group(['prefix' => 'enjoy', 'as' => 'enjoy.' ], function () {
//   PRODUTOS
    Route::resource('products', ProductsController::class, ['only' => [
    'index', 'create', 'show', 'store', 'destroy'
    ]]);
    Route::put('/products/{id}', [ProductsController::class, 'edit']);

//  Clientes
    Route::resource('clients', ClientsController::class, ['only' => [
        'index', 'create', 'show', 'store', 'destroy'
    ]]);

    Route::put('/clients/{id}', [ClientsController::class, 'edit']);

//  Pedidos
    Route::resource('orders', OrdersController::class, ['only' => [
        'index', 'create', 'show', 'store', 'destroy'
    ]]);

    Route::put('/clients/{id}', [OrdersController::class, 'edit']);
});
