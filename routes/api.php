<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'enjoy', 'as' => 'enjoy.'], function () {
    Route::controller('App\Http\Controllers\Api\AuthController')->group(function () {
        Route::post('/auth/register', 'create_user')->name('register');
        Route::post('/auth/login', 'login_user')->name('login');
    });
    Route::controller('App\Http\Controllers\Api\ProductController')->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{product}', 'show')->name('show');
        Route::get('/sold/{product}', 'sold')->name('sold');
    });
    Route::controller('App\Http\Controllers\Api\OrderController')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{order}', 'show')->name('show');
        Route::get('/show/{order}/complete', 'show_details')->name('show.details');
    });
    Route::controller('App\Http\Controllers\Api\CustomerController')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{customer}', 'show')->name('show');
        Route::post('/create', 'store')->name('create');
        Route::put('/update/{customer}', 'update')->name('update');
        Route::delete('/delete/{customer}', 'destroy')->name('delete');

        Route::get('/address/', 'address_index')->name('address_index');
        Route::get('/address/show/{address}', 'address_show')->name('address_show');
    });
    Route::controller('App\Http\Controllers\Api\CategoryController')->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{category}', 'show')->name('show');
        Route::get('/show/tree/{category}', 'show_tree')->name('show');
        Route::post('/create', 'store')->name('create');
        Route::put('/update/{category}', 'update')->name('update');
        Route::delete('/delete/{category}', 'destroy')->name('delete');
    });
});

Route::fallback(function () {
    return response()->json(['data' => [], 'success' => false, 'status' => 404, 'message' => 'Invalid Route'], 404);
});
