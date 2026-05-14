<?php

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;

// ── Setup compartido ──────────────────────────────────────────────────────────

beforeEach(function () {
    $this->admin       = User::factory()->create(['type' => 1]);
    $this->regularUser = User::factory()->create(['type' => 0]);
    $this->uploadedFiles = [];
});

afterEach(function () {
    // El controlador usa move() directo al filesystem, no Storage::fake().
    // Eliminamos los archivos creados durante los tests para no ensuciar public/videos/.
    foreach ($this->uploadedFiles as $filename) {
        $path = public_path('videos/' . $filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }
});

// ── CASO 1 - Registrar video correctamente ────────────────────────────────────

test('admin can upload a valid mp4 and the record and file are created', function () {
    $file = UploadedFile::fake()->create('test_video.mp4', 100, 'video/mp4');

    $response = $this->actingAs($this->admin)
        ->post(route('video.store'), ['video' => $file]);

    $response->assertRedirect(route('video.index'));
    $response->assertSessionHas('success', 'Video subido exitosamente.');

    $video = Video::where('route', 'LIKE', '%test_video.mp4')
        ->orderBy('id', 'desc')
        ->first();

    $this->assertNotNull($video);
    $this->assertEquals(1, $video->active);

    $this->uploadedFiles[] = $video->route;
    $this->assertTrue(file_exists(public_path('videos/' . $video->route)));
});

// ── CASO 2 - Validación sin archivo ──────────────────────────────────────────

test('store validation fails when no file is sent', function () {
    $countBefore = Video::count();

    $response = $this->actingAs($this->admin)
        ->post(route('video.store'), []);

    $response->assertSessionHasErrors(['video']);
    $this->assertDatabaseCount('videos', $countBefore);
});

// ── CASO 3 - Validación de formato inválido ───────────────────────────────────

test('store validation fails when file format is not a valid video', function () {
    $countBefore = Video::count();
    $file        = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

    $response = $this->actingAs($this->admin)
        ->post(route('video.store'), ['video' => $file]);

    $response->assertSessionHasErrors(['video']);
    $this->assertDatabaseCount('videos', $countBefore);
});

// ── CASO 4 - Visualizar lista de videos ──────────────────────────────────────

test('admin can view the video list and the view contains database records', function () {
    $video1 = Video::create(['route' => '__test_v1.mp4', 'active' => 1]);
    $video2 = Video::create(['route' => '__test_v2.mp4', 'active' => 0]);

    $response = $this->actingAs($this->admin)
        ->get(route('video.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.video.index');
    $response->assertViewHas('videos');

    $videos = $response->viewData('videos');
    expect($videos->contains('id', $video1->id))->toBeTrue();
    expect($videos->contains('id', $video2->id))->toBeTrue();
});

// ── CASO 5 - Toggle activo/inactivo ──────────────────────────────────────────

test('toggle switches video active from 1 to 0 and back to 1', function () {
    $video = Video::create(['route' => '__test_toggle.mp4', 'active' => 1]);

    // Primera llamada: 1 → 0
    $response = $this->actingAs($this->admin)
        ->post(route('video.toggle', ['id' => $video->id]));

    $response->assertOk();
    $response->assertJson(['active' => 0]);
    $this->assertDatabaseHas('videos', ['id' => $video->id, 'active' => 0]);

    // Segunda llamada: 0 → 1
    $response = $this->actingAs($this->admin)
        ->post(route('video.toggle', ['id' => $video->id]));

    $response->assertOk();
    $response->assertJson(['active' => 1]);
    $this->assertDatabaseHas('videos', ['id' => $video->id, 'active' => 1]);
});

// ── CASO 6 - Eliminar video ───────────────────────────────────────────────────

test('deleting a video removes the database record and the physical file', function () {
    if (!is_dir(public_path('videos'))) {
        mkdir(public_path('videos'), 0755, true);
    }

    $filename = '__test_delete_' . time() . '.mp4';
    file_put_contents(public_path('videos/' . $filename), 'fake video content');
    $this->uploadedFiles[] = $filename; // fallback si el test falla antes del DELETE

    $video = Video::create(['route' => $filename, 'active' => 1]);

    $response = $this->actingAs($this->admin)
        ->delete(route('video.destroy', ['id' => $video->id]));

    $response->assertRedirect(route('video.index'));
    $response->assertSessionHas('success', 'Video eliminado correctamente.');

    $this->assertDatabaseMissing('videos', ['id' => $video->id]);
    $this->assertFalse(file_exists(public_path('videos/' . $filename)));
});

// ── CASO 7 - Restricción de acceso ───────────────────────────────────────────

test('unauthenticated user is redirected to login when accessing videos', function () {
    $this->get(route('video.index'))
        ->assertRedirect('/login');
});

test('non-admin user gets 403 when accessing videos', function () {
    $this->actingAs($this->regularUser)
        ->get(route('video.index'))
        ->assertStatus(403);
});
