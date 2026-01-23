@extends('layouts.app')


@section('title', 'Editar perfil | AROMA')


@section('body-class', 'profile-body')


@section('content')

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary btn-sm">
                    ← Volver
                </a>
                <h4 class="mb-0">Mis direcciones</h4>
            </div>

            <button class="btn btn-dark" onclick="openCreateModal()" data-bs-toggle="modal" data-bs-target="#addressModal">
                + Agregar dirección
            </button>

        </div>


        <div class="row g-4">
            @forelse (Auth::user()->locations as $address)
                <div class="col-md-6 col-lg-4">
                    <div class="address-card">
                        <div class="address-card-body">
                            <h6 class="fw-bold mb-1">{{ $address->province }}</h6>
                            <h6 class="mb-1 text-muted small">
                                {{ $address->canton }} -
                                {{ $address->district }} -
                                {{ $address->zipcode }}
                            </h6>
                            <p>
                                {{ $address->detail }}
                            </p>

                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-sm btn-outline-dark" onclick='openEditModal(@json($address))'
                                    data-bs-toggle="modal" data-bs-target="#addressModal">
                                    Editar
                                </button>


                                <form method="POST" action="{{ route('location.destroy', $address->idlocation) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Eliminar esta dirección?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No tienes direcciones registradas.</p>
            @endforelse
        </div>
    </div>


    {{-- Modal para agregar o editar una nueva direccion --}}
    <div class="modal fade" id="addressModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalTitle">Agregar dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="addressForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="addressFormMethod" value="POST">

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Provincia</label>
                                <input type="text" name="province" id="province" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cantón</label>
                                <input type="text" name="canton" id="canton" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Distrito</label>
                                <input type="text" name="district" id="district" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Dirección exacta</label>
                                <textarea name="detail" id="detail" class="form-control" rows="2" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Código Postal</label>
                                <input type="text" name="zipcode" id="zipcode" maxlength="5" class="form-control"
                                    required>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-dark" id="addressSubmitBtn">
                            Guardar dirección
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <script>
        function openCreateModal() {
            document.getElementById('addressModalTitle').innerText = 'Agregar dirección';
            document.getElementById('addressSubmitBtn').innerText = 'Guardar dirección';

            const form = document.getElementById('addressForm');
            form.action = "{{ route('location.store') }}";
            document.getElementById('addressFormMethod').value = 'POST';

            form.reset();
        }

        function openEditModal(address) {
            document.getElementById('addressModalTitle').innerText = 'Editar dirección';
            document.getElementById('addressSubmitBtn').innerText = 'Actualizar dirección';

            const form = document.getElementById('addressForm');
            form.action = `/location/${address.idlocation}/update`;
            document.getElementById('addressFormMethod').value = 'PUT';

            document.getElementById('province').value = address.province;
            document.getElementById('canton').value = address.canton;
            document.getElementById('district').value = address.district;
            document.getElementById('detail').value = address.detail;
            document.getElementById('zipcode').value = address.zipcode;
        }
    </script>



@endsection
