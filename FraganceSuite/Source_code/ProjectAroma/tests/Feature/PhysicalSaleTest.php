<?php

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;

// ── Setup compartido ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->user = User::factory()->create(['type' => 0]);

    $this->product = Product::create([
        'name'             => 'Perfume Test',
        'brand'            => 'TestBrand',
        'category'         => 'Hombre',
        'pathimg'          => 'https://example.com/img.jpg',
        'price'            => 5000,
        'stock'            => 10,
        'active'           => true,
        'decant'           => false,
        'description'      => '',
        'shortDescription' => '',
    ]);
});

// ── ACCESO ────────────────────────────────────────────────────────────────────

test('guest cannot access the physical sale form', function () {
    $this->get('/physical-sales')->assertRedirect('/login');
});

test('guest cannot post to physical-sales', function () {
    // postJson envía Accept: application/json → Laravel devuelve 401 en vez de redirect
    $this->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 5000,
    ])->assertUnauthorized();
});

test('authenticated user can view the physical sale form', function () {
    $this->actingAs($this->user)
        ->get('/physical-sales')
        ->assertStatus(200)
        ->assertViewIs('dashboard.sales.physical-sales');
});

test('physical sale form receives the active products with stock', function () {
    Product::create([
        'name'             => 'Sin Stock',
        'brand'            => 'TestBrand',
        'category'         => 'Hombre',
        'pathimg'          => 'https://example.com/img2.jpg',
        'price'            => 3000,
        'stock'            => 0,
        'active'           => true,
        'decant'           => false,
        'description'      => '',
        'shortDescription' => '',
    ]);

    Product::create([
        'name'             => 'Inactivo',
        'brand'            => 'TestBrand',
        'category'         => 'Hombre',
        'pathimg'          => 'https://example.com/img3.jpg',
        'price'            => 2000,
        'stock'            => 5,
        'active'           => false,
        'decant'           => false,
        'description'      => '',
        'shortDescription' => '',
    ]);

    $response = $this->actingAs($this->user)->get('/physical-sales');
    $products = $response->viewData('products');

    expect($products->every(fn ($p) => $p->active && $p->stock > 0))->toBeTrue();
});

// ── VENTA EXITOSA ─────────────────────────────────────────────────────────────

test('valid physical sale returns success JSON response', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 2, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 10000,
    ])->assertStatus(200)->assertJson(['success' => true]);
});

test('valid physical sale creates an order in the database', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 5000,
    ]);

    $this->assertDatabaseHas('order', [
        'sale_type' => 'physical',
        'total'     => 5000,
        'iduser'    => $this->user->id,
    ]);
});

test('valid physical sale creates order detail with correct data', function () {
    $response = $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 2, 'price' => 5000]],
        'payment_method' => 'sinpe',
        'total'          => 10000,
    ]);

    $orderId = $response->json('order_id');
    $this->assertDatabaseHas('orderdetail', [
        'idorder'   => $orderId,
        'idproduct' => $this->product->idproduct,
        'quantity'  => 2,
        'price'     => 5000,
    ]);
});

test('physical sale decrements product stock by the purchased quantity', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 3, 'price' => 5000]],
        'payment_method' => 'sinpe',
        'total'          => 15000,
    ]);

    $this->product->refresh();
    expect($this->product->stock)->toBe(7);
});

test('physical sale associates the order to the authenticated user', function () {
    $other = User::factory()->create(['type' => 0]);

    $response = $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'transferencia',
        'total'          => 5000,
    ]);

    $orderId = $response->json('order_id');
    $this->assertDatabaseHas('order', ['idorder' => $orderId, 'iduser' => $this->user->id]);
    $this->assertDatabaseMissing('order', ['idorder' => $orderId, 'iduser' => $other->id]);
});

test('payment method is stored uppercased in the database', function () {
    $response = $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'sinpe',
        'total'          => 5000,
    ]);

    $orderId = $response->json('order_id');
    $this->assertDatabaseHas('order', ['idorder' => $orderId, 'purchasemethod' => 'SINPE']);
});

test('physical sale accepts efectivo as payment method', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 5000,
    ])->assertStatus(200)->assertJson(['success' => true]);
});

test('physical sale accepts transferencia as payment method', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'transferencia',
        'total'          => 5000,
    ])->assertStatus(200)->assertJson(['success' => true]);
});

test('physical sale with multiple items creates one order detail per item', function () {
    $product2 = Product::create([
        'name'             => 'Perfume Test 2',
        'brand'            => 'TestBrand',
        'category'         => 'Mujer',
        'pathimg'          => 'https://example.com/img2.jpg',
        'price'            => 3000,
        'stock'            => 5,
        'active'           => true,
        'decant'           => false,
        'description'      => '',
        'shortDescription' => '',
    ]);

    $response = $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [
            ['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000],
            ['idproduct' => $product2->idproduct, 'qty' => 2, 'price' => 3000],
        ],
        'payment_method' => 'efectivo',
        'total'          => 11000,
    ]);

    $response->assertStatus(200)->assertJson(['success' => true]);
    $orderId = $response->json('order_id');
    expect(OrderDetail::where('idorder', $orderId)->count())->toBe(2);
});

test('physical sale decrements stock for each item in the cart', function () {
    $product2 = Product::create([
        'name'             => 'Perfume Test 2',
        'brand'            => 'TestBrand',
        'category'         => 'Mujer',
        'pathimg'          => 'https://example.com/img2.jpg',
        'price'            => 3000,
        'stock'            => 5,
        'active'           => true,
        'decant'           => false,
        'description'      => '',
        'shortDescription' => '',
    ]);

    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [
            ['idproduct' => $this->product->idproduct, 'qty' => 2, 'price' => 5000],
            ['idproduct' => $product2->idproduct, 'qty' => 3, 'price' => 3000],
        ],
        'payment_method' => 'sinpe',
        'total'          => 19000,
    ]);

    $this->product->refresh();
    $product2->refresh();
    expect($this->product->stock)->toBe(8);
    expect($product2->stock)->toBe(2);
});

// ── VALIDACIÓN DE ENTRADA ─────────────────────────────────────────────────────

test('physical sale fails with invalid payment method', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'paypal',
        'total'          => 5000,
    ])->assertStatus(422);
});

test('physical sale fails when items array is empty', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [],
        'payment_method' => 'efectivo',
        'total'          => 0,
    ])->assertStatus(422);
});

test('physical sale fails when items field is missing', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'payment_method' => 'efectivo',
        'total'          => 5000,
    ])->assertStatus(422);
});

test('physical sale fails when payment_method is missing', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items' => [['idproduct' => $this->product->idproduct, 'qty' => 1, 'price' => 5000]],
        'total' => 5000,
    ])->assertStatus(422);
});

test('physical sale fails when item quantity is less than one', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 0, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 0,
    ])->assertStatus(422);
});

// ── ERRORES DE NEGOCIO ────────────────────────────────────────────────────────

test('physical sale returns 422 when product does not exist', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => 99999, 'qty' => 1, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 5000,
    ])->assertStatus(422);
});

test('physical sale returns 422 when stock is insufficient', function () {
    $response = $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 99, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 495000,
    ]);

    $response->assertStatus(422)->assertJson(['success' => false]);
    expect($response->json('error'))->toContain('Stock insuficiente');
});

test('stock is not decremented when sale fails due to insufficient stock', function () {
    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 99, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 495000,
    ]);

    $this->product->refresh();
    expect($this->product->stock)->toBe(10);
});

test('order is not created in database when transaction fails', function () {
    $ordersBefore = Order::where('iduser', $this->user->id)->count();

    $this->actingAs($this->user)->postJson('/physical-sales', [
        'items'          => [['idproduct' => $this->product->idproduct, 'qty' => 99, 'price' => 5000]],
        'payment_method' => 'efectivo',
        'total'          => 495000,
    ]);

    expect(Order::where('iduser', $this->user->id)->count())->toBe($ordersBefore);
});
