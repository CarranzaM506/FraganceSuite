<?php

use App\Models\Location;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

// ── Setup compartido ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->location = Location::create([
        'province' => 'San José',
        'canton'   => 'Central',
        'district' => 'Carmen',
        'detail'   => 'Casa de prueba',
        'zipcode'  => '10101',
        'iduser'   => $this->user->id,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

// ── CA-1: Los más vendidos del mes aparecen en la página principal ─────────────

test('best sellers of the month are shown on the main page', function () {
    $product = Product::create([
        'name'             => 'Perfume Best Seller Test A',
        'brand'            => 'Test Brand',
        'category'         => 'mujer',
        'pathimg'          => 'storage/img_a.jpg',
        'price'            => 9900,
        'stock'            => 100,
        'description'      => 'Descripción de prueba',
        'shortDescription' => 'Corta',
        'active'           => 1,
        'decant'           => 0,
    ]);

    $order = Order::create([
        'date'           => now()->format('Y-m-d'),
        'state'          => 1,
        'total'          => 9900 * 9999,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-BS1-' . uniqid(),
        'iduser'         => $this->user->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    OrderDetail::create([
        'idorder'   => $order->idorder,
        'idproduct' => $product->idproduct,
        'quantity'  => 9999,
        'price'     => 9900,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertViewHas('bestSellers', fn ($bestSellers) =>
        $bestSellers->contains('idproduct', $product->idproduct)
    );
});

// ── CA-2: Los productos están ordenados por cantidad vendida (descendente) ─────

test('best sellers are ordered by total sold quantity descending', function () {
    $lowSeller = Product::create([
        'name'             => 'Perfume Low Seller Test',
        'brand'            => 'Test Brand',
        'category'         => 'hombre',
        'pathimg'          => 'storage/low.jpg',
        'price'            => 5000,
        'stock'            => 50,
        'description'      => 'Test',
        'shortDescription' => 'Short',
        'active'           => 1,
        'decant'           => 0,
    ]);

    $highSeller = Product::create([
        'name'             => 'Perfume High Seller Test',
        'brand'            => 'Test Brand',
        'category'         => 'hombre',
        'pathimg'          => 'storage/high.jpg',
        'price'            => 5000,
        'stock'            => 50,
        'description'      => 'Test',
        'shortDescription' => 'Short',
        'active'           => 1,
        'decant'           => 0,
    ]);

    $orderLow = Order::create([
        'date'           => now()->format('Y-m-d'),
        'state'          => 1,
        'total'          => 5000 * 9998,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-LOW-' . uniqid(),
        'iduser'         => $this->user->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    OrderDetail::create([
        'idorder'   => $orderLow->idorder,
        'idproduct' => $lowSeller->idproduct,
        'quantity'  => 9998,
        'price'     => 5000,
    ]);

    $orderHigh = Order::create([
        'date'           => now()->format('Y-m-d'),
        'state'          => 1,
        'total'          => 5000 * 9999,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-HIGH-' . uniqid(),
        'iduser'         => $this->user->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    OrderDetail::create([
        'idorder'   => $orderHigh->idorder,
        'idproduct' => $highSeller->idproduct,
        'quantity'  => 9999,
        'price'     => 5000,
    ]);

    $response = $this->get('/');
    $response->assertStatus(200);

    $bestSellers = $response->viewData('bestSellers');
    $testIds     = [$lowSeller->idproduct, $highSeller->idproduct];
    $filtered    = $bestSellers->filter(fn ($p) => in_array($p->idproduct, $testIds))->values();

    expect($filtered->count())->toBe(2);
    expect($filtered->first()->idproduct)->toBe($highSeller->idproduct);
    expect($filtered->last()->idproduct)->toBe($lowSeller->idproduct);
});

// ── CA-3: La información del producto se muestra correctamente ────────────────

test('best seller product information is correct', function () {
    $product = Product::create([
        'name'             => 'Perfume Info Test XYZ',
        'brand'            => 'Brand Info',
        'category'         => 'mujer',
        'pathimg'          => 'storage/info_product.jpg',
        'price'            => 15000,
        'stock'            => 30,
        'description'      => 'Descripción info',
        'shortDescription' => 'Corta',
        'active'           => 1,
        'decant'           => 0,
    ]);

    $order = Order::create([
        'date'           => now()->format('Y-m-d'),
        'state'          => 1,
        'total'          => 15000 * 9999,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-INFO-' . uniqid(),
        'iduser'         => $this->user->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    OrderDetail::create([
        'idorder'   => $order->idorder,
        'idproduct' => $product->idproduct,
        'quantity'  => 9999,
        'price'     => 15000,
    ]);

    $response = $this->get('/');
    $response->assertStatus(200);

    $found = $response->viewData('bestSellers')
        ->firstWhere('idproduct', $product->idproduct);

    expect($found)->not->toBeNull();
    expect($found->name)->toBe('Perfume Info Test XYZ');
    expect((float) $found->price)->toBe(15000.0);
    expect($found->pathimg)->toBe('storage/info_product.jpg');
});

// ── CA-4: Sin ventas en el mes, la colección de más vendidos está vacía ────────

test('best sellers collection is empty when there are no sales in the current month', function () {
    // Simular un mes histórico sin ningún dato de ventas
    Carbon::setTestNow(Carbon::create(2000, 1, 15));

    $response = $this->get('/');
    $response->assertStatus(200);

    expect($response->viewData('bestSellers')->isEmpty())->toBeTrue();
});
