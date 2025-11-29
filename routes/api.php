<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/statistik', [OrderController::class, 'getStats']);
    Route::get('/stok', [StokController::class, 'index']);
    Route::get('/outlets/list-all', [OutletController::class, 'listAll']);
    Route::post('/stok', [StokController::class, 'updateStok']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/kategori', [CategoryController::class, 'store']);
    Route::post('/products', [ProdukController::class, 'store']);
    Route::post('/products/{product}', [ProdukController::class, 'update']);
    Route::post('/outlets', [OutletController::class, 'store']);
    Route::delete('/outlets/{id}', [OutletController::class, 'destroy']);
    Route::put('/outlets/{id}', [OutletController::class, 'update']);
    Route::delete('/products/{product}', [ProdukController::class, 'destroy']);

    Route::prefix('admin')->group(function(){
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);

        Route::get('/categories', [CategoryManagementController::class, 'index']);
        Route::post('/categories', [CategoryManagementController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryManagementController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryManagementController::class, 'destroy']);
    });

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{pesanan}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/pesanan/{pesanan}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{pesanan}/status', [OrderController::class, 'updateStatus']);
});

Route::get('/products', [ProdukController::class, 'index']);
Route::get('/products/{id}', [ProdukController::class, 'show']);
Route::get('/kategori', [CategoryController::class, 'index']);

Route::get('/kategori/{category}/products', [ProdukController::class, 'getProductsByCategory']);


Route::get('/outlets', [OutletController::class, 'index']);
Route::get('/cities', [OutletController::class, 'cities']);
Route::get('/totalProduk', [ProdukController::class, 'countProduct']);
Route::get('/totalOutlet', [OutletController::class, 'countOutlet']);
Route::get('/totalKategori', [CategoryController::class, 'countCategory']);
Route::get('/totalProdukperKategori', [CategoryController::class, 'summary']);


Route::prefix('/auth')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


