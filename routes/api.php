<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProdukController::class, 'index']);
Route::get('/products/{product}', [ProdukController::class, 'show']);
Route::get('/kategori', [CategoryController::class, 'index']);
Route::post('/kategori', [CategoryController::class, 'store']);
Route::post('/products', [ProdukController::class, 'store']);
Route::post('/products/{product}', [ProdukController::class, 'update']);
Route::delete('/products/{product}', [ProdukController::class, 'destroy']);

Route::get('/kategori/{category}/products', [ProdukController::class, 'getProductsByCategory']);

Route::post('/outlets', [OutletController::class, 'store']);
Route::delete('/outlets/{id}', [OutletController::class, 'destroy']);
Route::put('/outlets/{id}', [OutletController::class, 'update']);
Route::get('/outlets', [OutletController::class, 'index']);
Route::get('/cities', [OutletController::class, 'cities']);

Route::prefix('/auth')->group(function(){
    Route::post('/login', [AuthController::class, 'login']);
});