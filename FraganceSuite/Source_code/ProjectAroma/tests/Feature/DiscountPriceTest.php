<?php

use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->product = DB::table('product')->where('active', 1)->first();

    $this->originalDiscountId = $this->product->iddiscount ?? null;

    $this->activeDiscountId = DB::table('discount')->insertGetId([
        'value'     => 20,
        'startdate' => now()->subDay()->toDateString(),
        'enddate'   => now()->addDay()->toDateString(),
        'condition' => 'test-active',
    ]);

    $this->expiredDiscountId = DB::table('discount')->insertGetId([
        'value'     => 10,
        'startdate' => now()->subDays(10)->toDateString(),
        'enddate'   => now()->subDay()->toDateString(),
        'condition' => 'test-expired',
    ]);
});

afterEach(function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => $this->originalDiscountId]);
});

// CASO 1: Producto con promoción activa muestra precio tachado
test('product with active discount shows strikethrough price in catalog', function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => $this->activeDiscountId]);

    $response = $this->get(route('catalog.index') . '?search=' . urlencode($this->product->name));
    $response->assertOk();

    $products = $response->viewData('products')->getCollection();
    $item     = $products->firstWhere('idproduct', $this->product->idproduct);

    expect($item)->not->toBeNull();
    expect($item->discount)->not->toBeNull();
    expect((float) $item->discount->value)->toBe(20.0);

    $discountedPrice = $this->product->price * (1 - 20 / 100);

    $response->assertSee('old-price');
    $response->assertSee('new-price');

    expect($discountedPrice)->toBeLessThan($this->product->price);
});

// CASO 2: Producto sin promoción muestra solo precio normal
test('product without discount loads with null discount relation in catalog', function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => null]);

    $response = $this->get(route('catalog.index') . '?search=' . urlencode($this->product->name));
    $response->assertOk();

    $products = $response->viewData('products')->getCollection();
    $item     = $products->firstWhere('idproduct', $this->product->idproduct);

    expect($item)->not->toBeNull();
    expect($item->discount)->toBeNull();
});

// CASO 3: Promoción expirada no muestra precio tachado
test('expired discount is not loaded and product shows no strikethrough price', function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => $this->expiredDiscountId]);

    $response = $this->get(route('catalog.index') . '?search=' . urlencode($this->product->name));
    $response->assertOk();

    $products = $response->viewData('products')->getCollection();
    $item     = $products->firstWhere('idproduct', $this->product->idproduct);

    expect($item)->not->toBeNull();
    expect($item->discount)->toBeNull();
});

// CASO 4: Formato de precio correcto
test('discounted price is formatted with currency symbol and two decimals', function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => $this->activeDiscountId]);

    $discountedPrice = $this->product->price * (1 - 20 / 100);

    $response = $this->get(route('catalog.index') . '?search=' . urlencode($this->product->name));
    $response->assertOk();

    $response->assertSee('₡');
    $response->assertSee(number_format($discountedPrice, 2));
    $response->assertSee(number_format($this->product->price, 2));
});

// CASO 5: Página de detalle también muestra precio tachado
test('product detail page shows strikethrough price when discount is active', function () {
    DB::table('product')
        ->where('idproduct', $this->product->idproduct)
        ->update(['iddiscount' => $this->activeDiscountId]);

    $discountedPrice = $this->product->price * (1 - 20 / 100);

    $response = $this->get(route('product.show', $this->product->idproduct));
    $response->assertOk();

    $response->assertSee('old-price');
    $response->assertSee(number_format($discountedPrice, 2));

    expect($discountedPrice)->toBeLessThan($this->product->price);
});
