<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// ── Setup / Teardown ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->activeDiscountId = DB::table('discount')->insertGetId([
        'value'     => 20,
        'startdate' => Carbon::yesterday()->toDateString(),
        'enddate'   => Carbon::tomorrow()->toDateString(),
        'condition' => 'Descuento activo de prueba',
    ]);

    $this->expiredDiscountId = DB::table('discount')->insertGetId([
        'value'     => 15,
        'startdate' => Carbon::parse('-30 days')->toDateString(),
        'enddate'   => Carbon::yesterday()->toDateString(),
        'condition' => 'Descuento expirado de prueba',
    ]);

    $this->basePrice     = 50000;
    $this->discountValue = 20;

    $this->productWithDiscount = DB::table('product')->insertGetId([
        'name'             => '__test_precio_tachado_activo__',
        'brand'            => 'Test Brand',
        'category'         => 'Mujer',
        'pathimg'          => '',
        'price'            => $this->basePrice,
        'stock'            => 10,
        'description'      => 'Producto de prueba con descuento activo',
        'shortDescription' => 'Prueba',
        'active'           => 1,
        'decant'           => 0,
        'iddiscount'       => $this->activeDiscountId,
    ]);

    $this->productWithoutDiscount = DB::table('product')->insertGetId([
        'name'             => '__test_precio_tachado_sin_descuento__',
        'brand'            => 'Test Brand',
        'category'         => 'Hombre',
        'pathimg'          => '',
        'price'            => $this->basePrice,
        'stock'            => 5,
        'description'      => 'Producto de prueba sin descuento',
        'shortDescription' => 'Prueba',
        'active'           => 1,
        'decant'           => 0,
        'iddiscount'       => null,
    ]);

    $this->productWithExpiredDiscount = DB::table('product')->insertGetId([
        'name'             => '__test_precio_tachado_expirado__',
        'brand'            => 'Test Brand',
        'category'         => 'Mujer',
        'pathimg'          => '',
        'price'            => $this->basePrice,
        'stock'            => 5,
        'description'      => 'Producto de prueba con descuento expirado',
        'shortDescription' => 'Prueba',
        'active'           => 1,
        'decant'           => 0,
        'iddiscount'       => $this->expiredDiscountId,
    ]);
});

afterEach(function () {
    DB::table('product')->whereIn('idproduct', [
        $this->productWithDiscount,
        $this->productWithoutDiscount,
        $this->productWithExpiredDiscount,
    ])->delete();

    DB::table('discount')->whereIn('iddiscount', [
        $this->activeDiscountId,
        $this->expiredDiscountId,
    ])->delete();
});

// ── Caso 1: Producto con promoción activa muestra precio tachado ──────────────

test('product with active discount shows strikethrough price in catalog', function () {
    $discountedPrice = $this->basePrice * (1 - ($this->discountValue / 100));

    $response = $this->get(route('catalog'));
    $response->assertStatus(200);

    // Data layer: la relación discount se cargó con el filtro de fechas activas
    $response->assertViewHas('products', function ($products) {
        $p = $products->firstWhere('idproduct', $this->productWithDiscount);
        return $p !== null && $p->discount !== null;
    });

    // HTML layer: ambas clases de precio están presentes en la respuesta
    $response->assertSee('class="old-price"', false);
    $response->assertSee('class="new-price"', false);

    // El precio original aparece como valor tachado
    $response->assertSee(number_format($this->basePrice, 2));
    // El precio con descuento calculado aparece
    $response->assertSee(number_format($discountedPrice, 2));
    // El badge de porcentaje aparece
    $response->assertSee($this->discountValue . '% OFF');

    // El precio con descuento es menor que el precio original
    expect($discountedPrice)->toBeLessThan($this->basePrice);
});

// ── Caso 2: Producto sin promoción muestra solo precio normal ─────────────────

test('product without discount shows only regular price in catalog', function () {
    $response = $this->get(route('catalog'));
    $response->assertStatus(200);

    // Data layer: la relación discount es null para este producto
    $response->assertViewHas('products', function ($products) {
        $p = $products->firstWhere('idproduct', $this->productWithoutDiscount);
        return $p !== null && $p->discount === null;
    });

    // El precio normal aparece en la respuesta
    $response->assertSee(number_format($this->basePrice, 2));
});

// ── Caso 3: Promoción expirada no muestra precio tachado ─────────────────────

test('product with expired discount does not show strikethrough price in catalog', function () {
    $response = $this->get(route('catalog'));
    $response->assertStatus(200);

    // Data layer: el descuento expirado es excluido por el filtro de fechas
    $response->assertViewHas('products', function ($products) {
        $p = $products->firstWhere('idproduct', $this->productWithExpiredDiscount);
        return $p !== null && $p->discount === null;
    });

    // El precio sin descuento aparece normalmente
    $response->assertSee(number_format($this->basePrice, 2));
});

// ── Caso 4: Formato de precio correcto ───────────────────────────────────────

test('discount price is formatted with currency symbol and two decimal places', function () {
    $discountedPrice = $this->basePrice * (1 - ($this->discountValue / 100));

    $response = $this->get(route('catalog'));
    $response->assertStatus(200);

    // Ambos precios contienen el símbolo ₡ y dos decimales
    $response->assertSee('₡' . number_format($this->basePrice, 2), false);
    $response->assertSee('₡' . number_format($discountedPrice, 2), false);
});

// ── Caso 5: Página de detalle del producto también muestra precio tachado ─────

test('product detail page shows strikethrough price when discount is active', function () {
    $discountedPrice = $this->basePrice * (1 - ($this->discountValue / 100));

    $response = $this->get(route('product.show', $this->productWithDiscount));
    $response->assertStatus(200);

    // Data layer: la variable discountedPrice se pasa a la vista con un valor real
    $response->assertViewHas('discountedPrice', fn ($v) => $v !== null);

    // HTML layer: la clase old-price aparece con el precio original
    $response->assertSee('class="old-price"', false);

    // Ambos valores de precio aparecen en la respuesta
    $response->assertSee('₡' . number_format($this->basePrice, 2), false);
    $response->assertSee('₡' . number_format($discountedPrice, 2), false);
});
