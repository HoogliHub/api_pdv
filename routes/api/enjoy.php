<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ClientsController;

Route::group(['prefix' => 'enjoy', 'as' => 'enjoy.' ], function () {

    Route::resource('products', ProductsController::class, ['only' => [
    'index', 'create', 'show', 'store', 'destroy'
    ]]);
    Route::put('/products/{id}', [ProductsController::class, 'edit']);

    Route::resource('clients', ClientsController::class, ['only' => [
        'index', 'create', 'show', 'store', 'destroy'
    ]]);
    Route::put('/products/{id}', [ClientsController::class, 'edit']);
});
