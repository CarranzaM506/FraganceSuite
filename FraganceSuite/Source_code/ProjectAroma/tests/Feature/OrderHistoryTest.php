<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user  = User::factory()->create();
    $this->other = User::factory()->create();

    $this->locationId = DB::table('location')->insertGetId([
        'province' => 'San José',
        'canton'   => 'Central',
        'district' => 'Carmen',
        'detail'   => 'Casa de prueba',
        'zipcode'  => '10101',
        'iduser'   => $this->user->id,
    ]);
});

// CASO 1: Usuario autenticado con pedidos ve su historial
test('authenticated user with orders sees their history', function () {
    $myOrderId = DB::table('order')->insertGetId([
        'date'           => '2026-05-01',
        'state'          => 1,
        'total'          => 15000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'GUIDE-TEST-001',
        'iduser'         => $this->user->id,
        'idlocation'     => $this->locationId,
    ]);

    $otherOrderId = DB::table('order')->insertGetId([
        'date'           => '2026-05-02',
        'state'          => 1,
        'total'          => 9999,
        'purchasemethod' => 'SINPE',
        'guidenumber'    => 'GUIDE-OTHER-001',
        'iduser'         => $this->other->id,
        'idlocation'     => $this->locationId,
    ]);

    $response = $this->actingAs($this->user)->get(route('orders.history'));

    $response->assertOk();

    $orders = $response->viewData('orders');

    expect($orders->contains('idorder', $myOrderId))->toBeTrue();
    expect($orders->contains('idorder', $otherOrderId))->toBeFalse();
    expect($orders->every(fn ($o) => (int) $o->iduser === $this->user->id))->toBeTrue();
});

// CASO 2: Pedidos ordenados de más reciente a más antiguo
test('orders are sorted from most recent to oldest', function () {
    $oldOrderId = DB::table('order')->insertGetId([
        'date'           => '2026-01-10',
        'state'          => 1,
        'total'          => 5000,
        'purchasemethod' => 'SINPE',
        'guidenumber'    => '',
        'iduser'         => $this->user->id,
        'idlocation'     => $this->locationId,
    ]);

    $newOrderId = DB::table('order')->insertGetId([
        'date'           => '2026-05-20',
        'state'          => 1,
        'total'          => 10000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => '',
        'iduser'         => $this->user->id,
        'idlocation'     => $this->locationId,
    ]);

    $response = $this->actingAs($this->user)->get(route('orders.history'));

    $response->assertOk();

    $orders = $response->viewData('orders')
        ->filter(fn ($o) => in_array($o->idorder, [$oldOrderId, $newOrderId]))
        ->values();

    expect($orders->first()->idorder)->toBe($newOrderId);
    expect($orders->last()->idorder)->toBe($oldOrderId);
});

// CASO 3: Usuario sin pedidos ve el mensaje de estado vacío
test('user with no orders sees empty state message', function () {
    $response = $this->actingAs($this->user)->get(route('orders.history'));

    $response->assertOk();
    $response->assertSee('Aún no tienes pedidos registrados.');
});

// CASO 4a: Pedido con state=2 muestra "Enviado"
test('order with state 2 is displayed as Enviado', function () {
    DB::table('order')->insert([
        'date'           => '2026-05-01',
        'state'          => 2,
        'total'          => 20000,
        'purchasemethod' => 'PAYPAL',
        'guidenumber'    => 'GUIDE-SENT-001',
        'iduser'         => $this->user->id,
        'idlocation'     => $this->locationId,
    ]);

    $response = $this->actingAs($this->user)->get(route('orders.history'));

    $response->assertOk();
    $response->assertSee('Enviado');
});

// CASO 4b: Pedido con state=1 muestra "Pendiente"
test('order with state 1 is displayed as Pendiente', function () {
    DB::table('order')->insert([
        'date'           => '2026-05-01',
        'state'          => 1,
        'total'          => 20000,
        'purchasemethod' => 'SINPE',
        'guidenumber'    => '',
        'iduser'         => $this->user->id,
        'idlocation'     => $this->locationId,
    ]);

    $response = $this->actingAs($this->user)->get(route('orders.history'));

    $response->assertOk();
    $response->assertSee('Pendiente');
});

// CASO 5: Usuario no autenticado es redirigido al login
test('unauthenticated user is redirected to login', function () {
    $response = $this->get(route('orders.history'));

    $response->assertRedirect('/login');
});
