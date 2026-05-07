<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->create(['type' => 1]);
    $this->user  = User::factory()->create(['type' => 0]);

    $this->productId = DB::table('product')->insertGetId([
        'name'             => '__test_product__',
        'brand'            => '',
        'category'         => '',
        'pathimg'          => '',
        'price'            => 0,
        'stock'            => 0,
        'description'      => '',
        'shortDescription' => '',
        'active'           => 0,
        'decant'           => 0,
    ]);
});

afterEach(function () {
    DB::table('review')->where('idproduct', $this->productId)->delete();
    DB::table('product')->where('idproduct', $this->productId)->delete();
});

// Caso 1: El administrador puede ver el panel de reseñas registradas
test('admin can view the reviews management panel', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('dashboard.reviews.index'));

    $response->assertOk();
});

// Caso 2: El administrador puede eliminar una reseña
test('admin can delete a review', function () {
    $review = Review::create([
        'idproduct'  => $this->productId,
        'iduser'     => $this->admin->id,
        'rating'     => '4',
        'comment'    => 'Buen perfume',
        'is_blocked' => 0,
    ]);

    $response = $this->actingAs($this->admin)
        ->delete(route('dashboard.reviews.destroy', ['idreview' => $review->idreview]));

    $response->assertRedirect(route('dashboard.reviews.index', ['status' => 'blocked']));
    $response->assertSessionHas('status', 'Resena eliminada correctamente.');
});

// Caso 3: La reseña desaparece de la base de datos tras ser eliminada
test('deleted review is removed from the database', function () {
    $review = Review::create([
        'idproduct'         => $this->productId,
        'iduser'            => $this->admin->id,
        'rating'            => '3',
        'comment'           => 'Comentario de prueba',
        'is_blocked'        => 1,
        'moderation_reason' => 'Lenguaje ofensivo',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('dashboard.reviews.destroy', ['idreview' => $review->idreview]));

    $this->assertDatabaseMissing('review', ['idreview' => $review->idreview]);
});

// Caso 4: La reseña persiste cuando el usuario cancela la confirmación (no envía DELETE)
test('review persists when delete is not confirmed', function () {
    $review = Review::create([
        'idproduct'  => $this->productId,
        'iduser'     => $this->user->id,
        'rating'     => '5',
        'comment'    => 'Muy buena fragancia',
        'is_blocked' => 0,
    ]);

    // No se envía la petición DELETE (el usuario canceló el diálogo de confirmación)
    $this->assertDatabaseHas('review', ['idreview' => $review->idreview]);
});

// Caso 5: Un usuario sin rol de administrador no puede eliminar una reseña
test('regular user cannot delete a review', function () {
    $review = Review::create([
        'idproduct'  => $this->productId,
        'iduser'     => $this->user->id,
        'rating'     => '2',
        'comment'    => 'No me gustó',
        'is_blocked' => 0,
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('dashboard.reviews.destroy', ['idreview' => $review->idreview]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('review', ['idreview' => $review->idreview]);
});
