<?php

use App\Models\Location;
use App\Models\Order;
use App\Models\User;

// ── Helper ────────────────────────────────────────────────────────────────────

function createOrderFor(User $user, array $overrides = []): Order
{
    $location = Location::create([
        'province' => 'San José',
        'canton'   => 'Central',
        'district' => 'Carmen',
        'detail'   => 'Dirección de prueba',
        'zipcode'  => '10101',
        'iduser'   => $user->id,
    ]);

    return Order::create(array_merge([
        'date'           => '2025-05-01',
        'state'          => 1,
        'total'          => 10000,
        'purchasemethod' => 'PayPal',
        'guidenumber'    => '',
        'iduser'         => $user->id,
        'idlocation'     => $location->idlocation,
    ], $overrides));
}

// ── CASO 1 - Usuario con pedidos ve su historial ──────────────────────────────

test('authenticated user sees only their own orders', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    createOrderFor($user, ['guidenumber' => 'MYGUIDE-001']);
    createOrderFor($other, ['guidenumber' => 'OTHERGUIDE-999']);

    $response = $this->actingAs($user)->get(route('orders.history'));

    $response->assertOk();

    $orders = $response->viewData('orders');
    expect($orders->every(fn ($o) => $o->iduser === $user->id))->toBeTrue();
    $response->assertSee('MYGUIDE-001');
    $response->assertDontSee('OTHERGUIDE-999');
});

// ── CASO 2 - Pedidos ordenados de más reciente a más antiguo ──────────────────

test('orders are listed from most recent to oldest', function () {
    $user = User::factory()->create();

    $older = createOrderFor($user, ['date' => '2024-01-10']);
    $newer = createOrderFor($user, ['date' => '2025-12-25']);

    $response = $this->actingAs($user)->get(route('orders.history'));

    $orders = $response->viewData('orders');
    expect($orders->first()->idorder)->toBe($newer->idorder);
});

// ── CASO 3 - Usuario sin pedidos ve mensaje vacío ────────────────────────────

test('user with no orders sees empty message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('orders.history'))
        ->assertOk()
        ->assertSee('Aún no tienes pedidos registrados.');
});

// ── CASO 4 - Estado se muestra correctamente ─────────────────────────────────

test('order with state 2 shows Enviado in the view', function () {
    $user = User::factory()->create();

    createOrderFor($user, ['state' => 2]);

    $this->actingAs($user)
        ->get(route('orders.history'))
        ->assertOk()
        ->assertSee('Enviado');
});

// ── CASO 5 - Usuario no autenticado es redirigido al login ───────────────────

test('unauthenticated user is redirected to login', function () {
    $this->get(route('orders.history'))
        ->assertRedirect('/login');
});
