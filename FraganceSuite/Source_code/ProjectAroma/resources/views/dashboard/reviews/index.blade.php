@extends('partsAdmin.header')

@section('title', 'Moderar Reseñas')

@section('content')
    <main class="content-wrap p-3 p-md-4">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('dashboard.reviews.index', ['status' => 'blocked']) }}"
                class="btn btn-sm {{ $status === 'blocked' ? 'btn-dark' : 'btn-outline-dark' }}">Bloqueadas</a>
            <a href="{{ route('dashboard.reviews.index', ['status' => 'visible']) }}"
                class="btn btn-sm {{ $status === 'visible' ? 'btn-dark' : 'btn-outline-dark' }}">Visibles</a>
            <a href="{{ route('dashboard.reviews.index', ['status' => 'all']) }}"
                class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">Todas</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Usuario</th>
                                <th>Rating</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $review)
                                <tr>
                                    <td>{{ $review->idreview }}</td>
                                    <td>{{ $review->product->name ?? 'Sin producto' }}</td>
                                    <td>
                                        <div>{{ trim(($review->user->name ?? '') . ' ' . ($review->user->lastname ?? '')) ?: 'Usuario' }}</div>
                                        <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $review->rating }}</td>
                                    <td style="max-width: 350px; white-space: normal;">{{ $review->comment ?: 'Sin comentario' }}</td>
                                    <td>
                                        @if ((int) $review->is_blocked === 1)
                                            <span class="badge bg-danger">Bloqueada</span>
                                        @else
                                            <span class="badge bg-success">Visible</span>
                                        @endif
                                    </td>
                                    <td>{{ $review->moderation_reason ?: '-' }}</td>
                                    <td>{{ optional($review->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('dashboard.reviews.destroy', $review->idreview) }}" class="d-inline delete-review-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No hay reseñas para este filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </main>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-review-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    openAdminDeleteConfirm('¿Eliminar esta reseña?', function() {
                        form.submit();
                    });
                });
            });
        });
    </script>
@endsection
