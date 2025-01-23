<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::controller(StoreController::class)->prefix('store')->group(function () {
    Route::get('/index', 'index');
    Route::get('/show/{id}', 'show');
    Route::post('/store', 'store');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

Route::controller(AddressController::class)->prefix('address')->group(function () {
    Route::get('/index', 'index');
    Route::get('/show/{id}', 'show');
    Route::post('/store', 'store');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});
