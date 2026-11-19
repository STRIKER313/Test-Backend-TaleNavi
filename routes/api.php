<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::get('me', 'me')->middleware('auth:sanctum');
});

Route::get('products', [CatalogController::class, 'products']);
Route::get('products/{id}', [CatalogController::class, 'product'])->whereNumber('id');
Route::get('categories', [CatalogController::class, 'categories']);

Route::prefix('cart')->middleware('auth:sanctum')->controller(CartController::class)->group(function (): void {
    Route::get('/', 'show');
    Route::post('items', 'store');
    Route::patch('items/{id}', 'update')->whereNumber('id');
    Route::delete('items/{id}', 'destroy')->whereNumber('id');
});

Route::prefix('orders')->middleware('auth:sanctum')->controller(OrderController::class)->group(function (): void {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('{id}', 'show')->whereNumber('id');
});
