<?php

use App\Http\Controllers\ControllerProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('product',ControllerProduct::class);

Route::get('/dashboard', function () {
    return view('dashboard.main');
})->name('dashboard');