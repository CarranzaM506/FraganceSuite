<?php

use App\Models\Order;
use App\Models\User;

// ── Setup compartido ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->admin       = User::factory()->create(['type' => 1]);
    $this->regularUser = User::factory()->create(['type' => 0]);

    $this->customer = User::factory()->create([
        'type'     => 0,
        'name'     => 'Juan',
        'lastname' => 'Pérez',
        'email'    => 'juan.perez@test.com',
        'phone'    => '88001122',
    ]);
});

// ── ACCESO Y AUTORIZACIÓN ─────────────────────────────────────────────────────

test('unauthenticated user is redirected to login when accessing pending shipments', function () {
    $this->get('/pending-shipments')->assertRedirect('/login');
});

test('non-admin user gets 403 when accessing pending shipments', function () {
    $this->actingAs($this->regularUser)->get('/pending-shipments')->assertStatus(403);
});

test('admin can access the pending shipments page', function () {
    $this->actingAs($this->admin)
        ->get('/pending-shipments')
        ->assertStatus(200)
        ->assertViewIs('dashboard.sales.pending-shipments');
});

test('non-admin cannot patch a shipment update', function () {
    $order = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $this->actingAs($this->regularUser)
        ->patch("/pending-shipments/{$order->idorder}", ['shipping_status' => 'in_progress'])
        ->assertStatus(403);
});

// ── FILTRADO POR ESTADO ───────────────────────────────────────────────────────

test('default status filter is pending when no param is provided', function () {
    $response = $this->actingAs($this->admin)->get('/pending-shipments');
    expect($response->viewData('status'))->toBe('pending');
});

test('invalid status param is normalized to pending', function () {
    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=inexistente');
    expect($response->viewData('status'))->toBe('pending');
});

test('pending filter shows only pending web orders', function () {
    $pendingOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $inProgressOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 3000,
        'purchasemethod'  => 'CARD',
        'sale_type'       => 'web',
        'shipping_status' => 'in_progress',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $pendingOrder->idorder))->toBeTrue();
    expect($orders->contains('idorder', $inProgressOrder->idorder))->toBeFalse();
});

test('in_progress filter shows only in-progress web orders', function () {
    $inProgressOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 3000,
        'purchasemethod'  => 'CARD',
        'sale_type'       => 'web',
        'shipping_status' => 'in_progress',
        'iduser'          => $this->customer->id,
    ]);

    $pendingOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=in_progress');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $inProgressOrder->idorder))->toBeTrue();
    expect($orders->contains('idorder', $pendingOrder->idorder))->toBeFalse();
});

test('completed filter shows only completed web orders', function () {
    $completedOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 8000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'completed',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=completed');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $completedOrder->idorder))->toBeTrue();
});

test('physical sale orders are excluded from pending shipments', function () {
    $physicalOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'EFECTIVO',
        'sale_type'       => 'physical',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $physicalOrder->idorder))->toBeFalse();
});

test('orders with state different from 1 are excluded', function () {
    $inactiveOrder = Order::create([
        'date'            => now(),
        'state'           => 0,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $inactiveOrder->idorder))->toBeFalse();
});

// ── BÚSQUEDA ─────────────────────────────────────────────────────────────────

test('search by customer first name returns matching orders', function () {
    $matchingOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $otherUser  = User::factory()->create(['type' => 0, 'name' => 'Carlos']);
    $otherOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 3000,
        'purchasemethod'  => 'CARD',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $otherUser->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending&search=Juan');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $matchingOrder->idorder))->toBeTrue();
    expect($orders->contains('idorder', $otherOrder->idorder))->toBeFalse();
});

test('search by customer email returns matching orders', function () {
    $matchingOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending&search=juan.perez@test.com');
    $orders   = $response->viewData('orders');

    expect($orders->contains('idorder', $matchingOrder->idorder))->toBeTrue();
});

test('search with no matches returns empty orders list', function () {
    $response = $this->actingAs($this->admin)->get('/pending-shipments?status=pending&search=zzz_no_existe_zzz');
    $orders   = $response->viewData('orders');

    expect($orders)->toBeEmpty();
});

// ── CONTEOS POR ESTADO ────────────────────────────────────────────────────────

test('counts reflect the number of web orders per shipping status', function () {
    Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 3000,
        'purchasemethod'  => 'CARD',
        'sale_type'       => 'web',
        'shipping_status' => 'in_progress',
        'iduser'          => $this->customer->id,
    ]);

    $response = $this->actingAs($this->admin)->get('/pending-shipments');
    $counts   = $response->viewData('counts');

    expect((int) ($counts['pending']     ?? 0))->toBeGreaterThanOrEqual(1);
    expect((int) ($counts['in_progress'] ?? 0))->toBeGreaterThanOrEqual(1);
});

// ── ACTUALIZACIÓN DE ENVÍO ────────────────────────────────────────────────────

test('admin can update shipping status of a web order', function () {
    $order = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $this->actingAs($this->admin)
        ->patch("/pending-shipments/{$order->idorder}", [
            'shipping_status' => 'in_progress',
            'guidenumber'     => 'GUIDE-001',
        ])->assertRedirect();

    $order->refresh();
    expect($order->shipping_status)->toBe('in_progress');
});

test('admin can update the guide number of an order', function () {
    $order = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $this->actingAs($this->admin)
        ->patch("/pending-shipments/{$order->idorder}", [
            'shipping_status' => 'in_progress',
            'guidenumber'     => 'CORREOS-ABC-789',
        ])->assertRedirect();

    $order->refresh();
    expect($order->guidenumber)->toBe('CORREOS-ABC-789');
});

test('update shipment redirects with success message', function () {
    $order = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $this->actingAs($this->admin)
        ->patch("/pending-shipments/{$order->idorder}", [
            'shipping_status' => 'completed',
        ])->assertRedirect()->assertSessionHas('success');
});

test('update shipment fails with invalid shipping status', function () {
    $order = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'PAYPAL',
        'sale_type'       => 'web',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    // Ruta web: la validación fallida redirige de vuelta con errores en sesión
    $this->actingAs($this->admin)
        ->patch("/pending-shipments/{$order->idorder}", [
            'shipping_status' => 'estado_invalido',
        ])->assertRedirect()->assertSessionHasErrors('shipping_status');
});

test('update shipment returns 404 for a non-existent order', function () {
    $this->actingAs($this->admin)
        ->patch('/pending-shipments/99999', [
            'shipping_status' => 'in_progress',
        ])->assertStatus(404);
});

test('update shipment returns 404 when order belongs to a physical sale', function () {
    $physicalOrder = Order::create([
        'date'            => now(),
        'state'           => 1,
        'total'           => 5000,
        'purchasemethod'  => 'EFECTIVO',
        'sale_type'       => 'physical',
        'shipping_status' => 'pending',
        'iduser'          => $this->customer->id,
    ]);

    $this->actingAs($this->admin)
        ->patch("/pending-shipments/{$physicalOrder->idorder}", [
            'shipping_status' => 'in_progress',
        ])->assertStatus(404);
});
