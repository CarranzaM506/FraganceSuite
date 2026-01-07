<?php

use App\Http\Controllers\ControllerImportProducts;
use App\Http\Controllers\ControllerProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



//Rutas de Administracion

Route::post('/import',[ControllerImportProducts::class,'import'])->name('product.import');

Route::resource('product',ControllerProduct::class);

Route::get('/dashboard', function () {
    return view('dashboard.main');
})->name('dashboard');
