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
    Route::apiResource('products', 'App\Http\Controllers\Api\ProductController')->only('index');
    Route::get('api/documentation', '\L5Swagger\Http\Controllers\SwaggerController@api');
});

Route::fallback(function () {
    return response()->json(['data' => [], 'success' => false, 'status' => 404, 'message' => 'Invalid Route', 'teste' => base_path('app\Http\Controllers\Api')], 404);
});
