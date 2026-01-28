<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ControllerImportProducts;
use App\Http\Controllers\ControllerProduct;
use App\Http\Controllers\ControllerDiscount;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\MainPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

// RUTA PRINCIPAL
Route::get('/', [MainPageController::class, 'index'])->name('mainPage');

// CATÁLOGO
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog');

// CARRITO
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// API PARA CARRITO
Route::get('/api/product/{id}', [CartController::class, 'getProductData']);
Route::get('/api/cart/preview', [CartController::class, 'getCartPreview'])->name('cart.preview');

// RUTAS DE ADMINISTRACIÓN
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', function () { return view('dashboard.main');})->name('dashboard');

    Route::post('/import', [ControllerImportProducts::class, 'import'])->name('product.import');

    //PRODCUTOS
    Route::resource('product', ControllerProduct::class);
    Route::resource('slider', SliderController::class);

    // PROMOCIONES / DESCUENTOS
    Route::resource('discount', ControllerDiscount::class);
    Route::get('discount/{id}/products', [ControllerDiscount::class, 'products'])->name('discount.products');
    Route::get('products/search', [ControllerDiscount::class, 'searchProducts'])->name('products.search');
});

//Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile',[ProfileController::class,'index'])->name('profile.index');
    Route::get('profile/edit',[ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update',[ProfileController::class,'update'])->name('profile.update');
    Route::get('/location', [LocationController::class, 'index'])->name('location.index');
    Route::post('/location/store', [LocationController::class, 'store'])->name('location.store');
    Route::put('location/{id}/update',[LocationController::class,'update'])->name('location.update');
    Route::delete('/location/{id}/delete',[LocationController::class,'destroy'])->name('location.destroy');
});

require __DIR__ . '/auth.php';
