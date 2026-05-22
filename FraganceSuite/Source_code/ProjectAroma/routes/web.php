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
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CodePromotionController;
use App\Http\Controllers\FavoriteController; 
use App\Http\Controllers\BrandController; 
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PhysicalSaleController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\InfoCarouselController;  
use Illuminate\Support\Facades\Route;

// RUTA PRINCIPAL
Route::get('/', [MainPageController::class, 'index'])->name('mainPage');

// PÁGINAS LEGALES
Route::get('/terminos-y-condiciones', fn() => view('legal.terms'))->name('legal.terms');
Route::get('/politica-de-privacidad', fn() => view('legal.privacy'))->name('legal.privacy');

// CATÁLOGO
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog');

// DETALLE DE PRODUCTO
Route::get('/producto/{id}', [ProductDetailController::class, 'show'])->name('product.show');
Route::get('/product', [ControllerProduct::class, 'index'])->name('product');

// CARRITO
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// API PARA CARRITO
Route::get('/api/product/{id}', [CartController::class, 'getProductData']);
Route::get('/api/cart/preview', [CartController::class, 'getCartPreview'])->name('cart.preview');

//API ver resenas
Route::get('/api/reviews/{productId}', [ReviewController::class, 'getByProduct']);

// RUTAS DE ADMINISTRACIÓN (PROTEGIDAS)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');
    Route::post('/import', [ControllerImportProducts::class, 'import'])->name('product.import');
    Route::resource('product', ControllerProduct::class);
    Route::resource('hero', HeroController::class)->except(['show']);
    Route::resource('discount', ControllerDiscount::class);
    Route::get('discount/{id}/products', [ControllerDiscount::class, 'products'])->name('discount.products');
    Route::get('products/search', [ControllerDiscount::class, 'searchProducts'])->name('products.search');
    Route::resource('promotionCode', CodePromotionController::class);
    Route::get('brands/sync', [BrandController::class, 'sync'])->name('brands.sync');
    Route::resource('brands', BrandController::class)->only(['index', 'edit', 'update']);
    Route::get('/dashboard/reviews', [ReviewController::class, 'adminIndex'])->name('dashboard.reviews.index');
    Route::patch('/dashboard/reviews/{idreview}/approve', [ReviewController::class, 'approve'])->name('dashboard.reviews.approve');
    Route::delete('/dashboard/reviews/{idreview}', [ReviewController::class, 'destroy'])->name('dashboard.reviews.destroy');

    // Videos
    Route::get('/video', [VideoController::class, 'index'])->name('video.index');
    Route::post('/video', [VideoController::class, 'store'])->name('video.store');
    Route::post('/video/{id}/toggle', [VideoController::class, 'toggle'])->name('video.toggle');
    Route::delete('/video/{id}', [VideoController::class, 'destroy'])->name('video.destroy');
    

    // Info Carousel
Route::get('/admin/info-carousel', [InfoCarouselController::class, 'index'])->name('admin.info-carousel.index');
Route::get('/admin/info-carousel/create', [InfoCarouselController::class, 'create'])->name('admin.info-carousel.create');
Route::post('/admin/info-carousel', [InfoCarouselController::class, 'store'])->name('admin.info-carousel.store');
Route::get('/admin/info-carousel/{id}/edit', [InfoCarouselController::class, 'edit'])->name('admin.info-carousel.edit');
Route::put('/admin/info-carousel/{id}', [InfoCarouselController::class, 'update'])->name('admin.info-carousel.update');
Route::delete('/admin/info-carousel/{id}', [InfoCarouselController::class, 'destroy'])->name('admin.info-carousel.destroy');
Route::post('/admin/info-carousel/toggle/{id}', [InfoCarouselController::class, 'toggle'])->name('admin.info-carousel.toggle');
Route::post('/admin/info-carousel/update-order', [InfoCarouselController::class, 'updateOrder'])->name('admin.info-carousel.update-order');
});

// RUTAS PROTEGIDAS USUARIOS (Requieren autenticación)
Route::middleware('auth')->group(function () {
    // Perfil y ubicaciones
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/location', [LocationController::class, 'index'])->name('location.index');
    Route::post('/location/store', [LocationController::class, 'store'])->name('location.store');
    Route::put('location/{id}/update', [LocationController::class, 'update'])->name('location.update');
    Route::delete('/location/{id}/delete', [LocationController::class, 'destroy'])->name('location.destroy');

    // API CARRITO
    Route::get('/api/cart', [CartController::class, 'get']);
    Route::post('/api/cart/add', [CartController::class, 'add']);
    Route::post('/api/cart/update', [CartController::class, 'update']);
    Route::post('/api/cart/remove', [CartController::class, 'remove']);
    Route::post('/api/cart/apply-code', [CartController::class, 'applyDiscountCode']);
    Route::post('/api/reviews', [ReviewController::class, 'store']);

    //Orders
    Route::get('/order/success/{id}', [OrderController::class, 'success']);
    Route::get('/orders/period', [OrderController::class, 'ordersByPeriod'])->name('orders.period');
    Route::resource('orders', OrderController::class);
    Route::get('/dailySales', [OrderController::class, "dailysales"])->name('dailySales');

    Route::get('/physical-sales', function () {
        $products = \App\Models\Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get();
        return view('dashboard.sales.physical-sales', compact('products'));
    })->name('physicalSales');
    Route::post('/physical-sales', [PhysicalSaleController::class, 'store'])->name('physicalSales.store');

    Route::post('/api/address', [LocationController::class, 'storeApi']);
    Route::get('/checkout', [CheckOutController::class, 'index'])->name('checkout');

    // FAVORITOS - Rutas específicas para favoritos
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites/check/{productId}', [FavoriteController::class, 'check'])->name('favorites.check');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    //ruta para pagar con paypal y guardar pedido
    Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
    Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder']);
});

require __DIR__ . '/auth.php';