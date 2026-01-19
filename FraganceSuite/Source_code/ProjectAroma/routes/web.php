<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ControllerImportProducts;
use App\Http\Controllers\ControllerProduct;
use App\Http\Controllers\ControllerDiscount;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\MainPageController;
use Illuminate\Support\Facades\Route;

// RUTA PRINCIPAL
Route::get('/', [MainPageController::class, 'index'])->name('mainPage');

// CATÁLOGO
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog');

// RUTAS DE ADMINISTRACIÓN
Route::get('/dashboard', function () {
    return view('dashboard.main');
})->name('dashboard');

Route::post('/import', [ControllerImportProducts::class,'import'])->name('product.import');

Route::resource('product', ControllerProduct::class);
Route::resource('slider', SliderController::class);

// PROMOCIONES / DESCUENTOS
Route::resource('discount', ControllerDiscount::class);
Route::get('discount/{id}/products', [ControllerDiscount::class, 'products'])->name('discount.products');
Route::get('products/search', [ControllerDiscount::class, 'searchProducts'])->name('products.search');
