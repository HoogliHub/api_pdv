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

Route::controller('App\Http\Controllers\Api\AuthController')->group(function () {
    Route::post('/auth/register', 'register')->name('register');
    Route::post('/auth/login', 'login')->name('login');
});
Route::middleware('auth:sanctum')->group(function () {
    //Attributes
    Route::controller('App\Http\Controllers\Api\ProductAttributesController')->prefix('products/attributes')->name('products_attributes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{attribute}', 'show')->name('show');
        Route::get('/values/{value}', 'show_values')->name('show.values');
        Route::post('/create', 'store')->name('create');
        Route::post('values/create', 'store_values')->name('create.values');
        Route::put('/{attribute}', 'update')->name('update');
        Route::put('/values/{value}', 'update_values')->name('update.values');
        Route::delete('/{attribute}', 'destroy')->name('delete');
        Route::delete('/values/{value}', 'destroy_values')->name('delete.values');
    });
    //Product Variation
    Route::controller('App\Http\Controllers\Api\ProductVariationController')->prefix('products/variants')->name('products_variants.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{variant}', 'show')->name('show');
        Route::post('/create', 'store')->name('create');
        Route::put('/{variant}', 'update')->name('update');
        Route::delete('/{variant}', 'destroy')->name('delete');
    });
    //Products
    Route::controller('App\Http\Controllers\Api\ProductController')->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{product}', 'show')->name('show');
        Route::get('/{product}/sold', 'sold')->name('sold');
        Route::post('/create', 'store')->name('create');
        Route::put('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('delete');
    });
    //Orders
    Route::controller('App\Http\Controllers\Api\OrderController')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{order}', 'show')->name('show');
        Route::get('/{order}/complete', 'show_details')->name('show.details');
        Route::post('/create', 'store')->name('create');
        Route::put('/{order}', 'update')->name('update');
        Route::delete('/{order}', 'destroy')->name('delete');
    });
    //Clients
    Route::controller('App\Http\Controllers\Api\CustomerController')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{customer}', 'show')->name('show');
        Route::post('/create', 'store')->name('create');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('delete');
        //Address
        Route::get('/addresses', 'address_index')->name('addresses.index');
        Route::get('/addresses/{address}', 'address_show')->name('addresses.show');
    });
    //Categories
    Route::controller('App\Http\Controllers\Api\CategoryController')->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{category}', 'show')->name('show');
        Route::get('/{category}/tree', 'show_tree')->name('show.tree');
        Route::post('/create', 'store')->name('create');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('delete');
    });
    //Coupon
    Route::controller('App\Http\Controllers\Api\CouponController')->prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{coupon}', 'show')->name('show');
        Route::get('/{coupon}/products', 'show_products')->name('show.products');
        Route::post('/create', 'store')->name('create');
        Route::put('/{coupon}', 'update')->name('update');
        Route::delete('/{coupon}', 'destroy')->name('delete');
    });
    //Colors
    Route::controller('App\Http\Controllers\Api\ColorsController')->prefix('colors')->name('colors.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{color}', 'show')->name('show');
        Route::post('/create', 'store')->name('create');
        Route::put('/{color}', 'update')->name('update');
        Route::delete('/{color}', 'destroy')->name('delete');
    });

    //User
    Route::controller('App\Http\Controllers\Api\AuthController')->group(function () {
        Route::get('/auth/logout', 'logout')->name('logout');
    });
});

Route::any('{segment}', function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ], 404);
})->where('segment', '.*');

Route::fallback(function () {
    return response()->json(['data' => [], 'success' => false, 'status' => 404, 'message' => 'Invalid Route'], 404);
});

Route::get('unauthorized', function () {
    return response()->json([
        'success' => false,
        'status' => 401,
        'message' => 'Unauthorized access. It is necessary to pass the access token.',
    ], 401);
})->name('unauthorized');
