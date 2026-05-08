<?php

use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

// ── Setup compartido ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => 1]);
    $this->regularUser = User::factory()->create(['type' => 0]);

    $this->location = Location::create([
        'province' => 'San José',
        'canton'   => 'Central',
        'district' => 'Carmen',
        'detail'   => 'Casa de prueba',
        'zipcode'  => '10101',
        'iduser'   => $this->admin->id,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

// ── ACCESO Y AUTORIZACIÓN (aplica a ambos gráficos) ──────────────────────────

test('unauthenticated user is redirected to login when accessing dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('non-admin user gets 403 when accessing dashboard', function () {
    $this->actingAs($this->regularUser)
        ->get('/dashboard')
        ->assertStatus(403);
});

// ── GRÁFICO DE VENTAS DIARIAS ─────────────────────────────────────────────────

test('daily sales chart is rendered with correct data when sales exist', function () {
    // Fecha histórica (sin datos previos en la BD real)
    Carbon::setTestNow(Carbon::create(2000, 6, 15, 12, 0, 0));

    Order::create([
        'date'           => '2000-06-15',
        'state'          => 1,
        'total'          => 5000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-DAILY-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    $response = $this->actingAs($this->admin)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.main');
    $response->assertViewHas('dailySales');
    $response->assertViewHas('dailyLabels');

    $dailySales = $response->viewData('dailySales');
    expect($dailySales)->toBeArray();
    expect(count($dailySales))->toBe(30);
    // El bucle va de i=29 a i=0; i=0 es "hoy" → índice 29 del array
    expect($dailySales[29])->toBe(5000.0);
});

test('daily sales data matches database totals per day', function () {
    Carbon::setTestNow(Carbon::create(2000, 6, 15, 12, 0, 0));

    // Dos órdenes el mismo día → deben sumarse
    Order::create([
        'date'           => '2000-06-15',
        'state'          => 1,
        'total'          => 3000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-D1-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);
    Order::create([
        'date'           => '2000-06-15',
        'state'          => 1,
        'total'          => 2000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-D2-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);
    // Orden de ayer
    Order::create([
        'date'           => '2000-06-14',
        'state'          => 1,
        'total'          => 7000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-D3-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    $response = $this->actingAs($this->admin)->get('/dashboard');
    $response->assertStatus(200);

    $dailySales = $response->viewData('dailySales');
    expect($dailySales[29])->toBe(5000.0); // hoy: 3000 + 2000
    expect($dailySales[28])->toBe(7000.0); // ayer: 7000
});

test('daily sales chart shows all zeros when no sales exist in period', function () {
    Carbon::setTestNow(Carbon::create(2000, 1, 15, 12, 0, 0));

    $response = $this->actingAs($this->admin)->get('/dashboard');
    $response->assertStatus(200);

    $dailySales = $response->viewData('dailySales');
    expect($dailySales)->toBeArray();
    expect(count($dailySales))->toBe(30);
    expect(array_sum($dailySales))->toBe(0);
});

// ── GRÁFICO DE VENTAS MENSUALES ───────────────────────────────────────────────

test('monthly sales chart is rendered with correct data when sales exist', function () {
    Carbon::setTestNow(Carbon::create(2000, 6, 15, 12, 0, 0));

    Order::create([
        'date'           => '2000-06-15',
        'state'          => 1,
        'total'          => 10000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-M1-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    $response = $this->actingAs($this->admin)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.main');
    $response->assertViewHas('monthlySales');
    $response->assertViewHas('meses');

    $monthlySales = $response->viewData('monthlySales');
    expect($monthlySales)->toBeArray();
    expect(count($monthlySales))->toBe(12);
    // Junio = mes 6, el array es 0-indexed → índice 5
    expect($monthlySales[5])->toBe(10000.0);
});

test('monthly sales data matches database totals per month', function () {
    Carbon::setTestNow(Carbon::create(2000, 6, 15, 12, 0, 0));

    // Dos órdenes en junio en días distintos → se suman
    Order::create([
        'date'           => '2000-06-10',
        'state'          => 1,
        'total'          => 8000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-M2A-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);
    Order::create([
        'date'           => '2000-06-15',
        'state'          => 1,
        'total'          => 4000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'TEST-M2B-' . uniqid(),
        'iduser'         => $this->admin->id,
        'idlocation'     => $this->location->idlocation,
    ]);

    $response = $this->actingAs($this->admin)->get('/dashboard');
    $response->assertStatus(200);

    $monthlySales = $response->viewData('monthlySales');
    expect($monthlySales[5])->toBe(12000.0); // junio: 8000 + 4000
});

test('monthly sales chart shows all zeros when no sales exist in year', function () {
    Carbon::setTestNow(Carbon::create(2000, 6, 15, 12, 0, 0));

    $response = $this->actingAs($this->admin)->get('/dashboard');
    $response->assertStatus(200);

    $monthlySales = $response->viewData('monthlySales');
    expect($monthlySales)->toBeArray();
    expect(count($monthlySales))->toBe(12);
    expect(array_sum($monthlySales))->toBe(0);
});
