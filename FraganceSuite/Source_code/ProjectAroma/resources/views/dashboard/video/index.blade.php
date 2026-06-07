@extends('partsAdmin.header')

@section('title', 'Videos')

@section('content')

<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-body">

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Gestión de Videos</h4>
                    <p class="text-muted mb-0">Sube y administra los videos de la tienda.</p>
                </div>
                <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#uploadVideoModal">
                    Agregar Video
                </button>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Desktop: tabla --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-striped table-hover table-bordered" id="videosTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Video</th>
                            <th>Activo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($videos as $video)
                            <tr>
                                <td>{{ $video->id }}</td>
                                <td>
                                    <video width="160" height="90" controls style="border-radius:6px;">
                                        <source src="{{ asset('videos/' . $video->route) }}" type="video/mp4">
                                    </video>
                                </td>
                                <td>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input toggle-active" type="checkbox" role="switch"
                                            data-id="{{ $video->id }}"
                                            {{ $video->active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-video-btn" 
                                        data-id="{{ $video->id }}"
                                        data-name="Video #{{ $video->id }}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: cards --}}
            <div class="d-md-none">
                @forelse ($videos as $video)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <p class="mb-1"><strong>ID:</strong> {{ $video->id }}</p>
                            <div class="mb-2">
                                <video width="100%" height="auto" controls style="border-radius:6px;">
                                    <source src="{{ asset('videos/' . $video->route) }}" type="video/mp4">
                                </video>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-active-mobile" type="checkbox" role="switch"
                                        data-id="{{ $video->id }}"
                                        {{ $video->active ? 'checked' : '' }}>
                                    <label class="form-check-label">Activo</label>
                                </div>
                                <button class="btn btn-sm btn-danger delete-video-btn-mobile" 
                                    data-id="{{ $video->id }}"
                                    data-name="Video #{{ $video->id }}">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted py-4">No hay videos cargados.</p>
                @endforelse
            </div>

        </div>
    </div>
</div>

{{-- Modal subir video --}}
<div class="modal fade" id="uploadVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="uploadVideoForm" method="POST" action="{{ route('video.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <label class="form-label">Archivo de video</label>
                    <input type="file" name="video" class="form-control" accept=".mp4,.webm,.mov" required>
                    <div class="form-text">Formatos permitidos: mp4, webm, mov. Máximo 200 MB.</div>

                    <div id="uploadProgress" class="mt-3 d-none">
                        <p class="text-center mb-2 small">Subiendo video, por favor espere...</p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 100%">Cargando...</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" id="btnSubir">Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        if (window.innerWidth >= 768) {
            $('#videosTable').DataTable({
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: { previous: '←', next: '→' },
                    emptyTable: 'No hay videos cargados.'
                },
                columnDefs: [{ orderable: false, targets: [2, 3] }]
            });
        }
    });

    // Re-abrir modal si hay errores de validación
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('uploadVideoModal'));
            modal.show();
        });
    @endif

    // Toggle activo/inactivo (desktop)
    document.querySelectorAll('.toggle-active').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id = this.dataset.id;
            const checkbox = this;

            fetch('/video/' + id + '/toggle', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                checkbox.checked = data.active == 1;
            })
            .catch(function () {
                checkbox.checked = !checkbox.checked;
            });
        });
    });

    // Toggle activo/inactivo (mobile)
    document.querySelectorAll('.toggle-active-mobile').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id = this.dataset.id;
            const checkbox = this;

            fetch('/video/' + id + '/toggle', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                checkbox.checked = data.active == 1;
            })
            .catch(function () {
                checkbox.checked = !checkbox.checked;
            });
        });
    });

    // Eliminar con SweetAlert2 (usando el mismo estilo que productos)
    document.querySelectorAll('.delete-video-btn, .delete-video-btn-mobile').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            openAdminDeleteConfirm('¿Eliminar ' + name + '?', function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/video/' + id;
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"> <input type="hidden" name="_method" value="DELETE">';
                document.body.appendChild(form);
                form.submit();
            });
        });
    });

    // Loader al subir video
    document.getElementById('uploadVideoForm').addEventListener('submit', function () {
        document.getElementById('uploadProgress').classList.remove('d-none');
        document.getElementById('btnSubir').disabled = true;
    });
</script>
@endsection