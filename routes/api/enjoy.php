<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;

Route::group(['prefix' => 'enjoy', 'as' => 'enjoy.' ], function () {

    Route::resource('products', ProductsController::class, ['only' => [
    'index', 'create', 'show', 'store', 'destroy'
    ]]);
    Route::put('/products/{id}', [ProductsController::class, 'edit']);
});
