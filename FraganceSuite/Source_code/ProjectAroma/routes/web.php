<?php

use App\Http\Controllers\ControllerImportProducts;
use App\Http\Controllers\ControllerProduct;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerDiscount;


Route::get('/', function () {
    return view('welcome');
});



//Rutas de Administracion

Route::post('/import',[ControllerImportProducts::class,'import'])->name('product.import');

Route::resource('product',ControllerProduct::class);

//Promociones (discounts)
Route::resource('discount', ControllerDiscount::class);
Route::get('discount/{id}/products', [ControllerDiscount::class, 'products'])->name('discount.products');
Route::get('products/search', [ControllerDiscount::class, 'searchProducts'])->name('products.search');

Route::get('/dashboard', function () {
    return view('dashboard.main');
})->name('dashboard');
