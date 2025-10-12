<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProdukController::class, 'index']);
Route::get('/products/{product}', [ProdukController::class, 'show']);
Route::post('/products', [ProdukController::class, 'store']);
Route::put('/products/{product}', [ProdukController::class, 'update']);
Route::delete('/products/{product}', [ProdukController::class, 'destroy']);

Route::prefix('/auth')->group(function(){
    Route::post('/login', [AuthController::class, 'login']);
});