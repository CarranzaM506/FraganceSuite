<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ControllerImportProducts;
use App\Http\Controllers\ControllerProduct;
use App\Http\Controllers\ControllerDiscount;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\HeroController; 
use App\Http\Controllers\MainPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CodePromotionController;
use Illuminate\Support\Facades\Route;

// RUTA PRINCIPAL
Route::get('/', [MainPageController::class, 'index'])->name('mainPage');

// CATÁLOGO
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog');

// DETALLE DE PRODUCTO - NUEVA RUTA
Route::get('/producto/{id}', [ProductDetailController::class, 'show'])->name('product.show');
Route::get('/product', [ControllerProduct::class, 'index'])->name('product');


// CARRITO
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// API PARA CARRITO
Route::get('/api/product/{id}', [CartController::class, 'getProductData']);
Route::get('/api/cart/preview', [CartController::class, 'getCartPreview'])->name('cart.preview');

// RUTAS DE ADMINISTRACIÓN
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', function () { return view('dashboard.main');})->name('dashboard');

    Route::post('/import', [ControllerImportProducts::class, 'import'])->name('product.import');

    // PRODUCTOS
    Route::resource('product', ControllerProduct::class);
    
    // HERO
    Route::resource('hero', HeroController::class)->except(['show']);

    // PROMOCIONES / DESCUENTOS
    Route::resource('discount', ControllerDiscount::class);
    Route::get('discount/{id}/products', [ControllerDiscount::class, 'products'])->name('discount.products');
    Route::get('products/search', [ControllerDiscount::class, 'searchProducts'])->name('products.search');
    Route::resource('promotionCode', CodePromotionController::class);
});

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile',[ProfileController::class,'index'])->name('profile.index');
    Route::get('profile/edit',[ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update',[ProfileController::class,'update'])->name('profile.update');
    Route::get('/location', [LocationController::class, 'index'])->name('location.index');
    Route::post('/location/store', [LocationController::class, 'store'])->name('location.store');
    Route::put('location/{id}/update',[LocationController::class,'update'])->name('location.update');
    Route::delete('/location/{id}/delete',[LocationController::class,'destroy'])->name('location.destroy');

    // API CARRITO
    Route::get('/api/cart', [CartController::class, 'get']);
    Route::post('/api/cart/add', [CartController::class, 'add']);
    Route::post('/api/cart/update', [CartController::class, 'update']);
    Route::post('/api/cart/remove', [CartController::class, 'remove']);
});

require __DIR__ . '/auth.php';