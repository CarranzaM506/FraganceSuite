@extends('layouts.app')

@section('page-name', 'Mis Direcciones')

@section('title', 'Mis direcciones | AROMA')

@section('body-class', 'profile-body')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('css/stylesLocation.css') }}">
@endsection

@section('content')
<div id="aroma-location-page">

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="location-page-header">
        <div class="location-page-header-left">
            <a href="{{ route('profile.index') }}" class="location-back-btn" title="Volver">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            </a>
            <h4>Mis direcciones</h4>
        </div>

        <button class="location-add-btn" onclick="openCreateModal()"
            data-bs-toggle="modal" data-bs-target="#addressModal">
            + Agregar dirección
        </button>
    </div>

    {{-- Listado de direcciones --}}
    <div class="row g-4">
        @forelse (Auth::user()->locations as $address)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="address-card">
                    <div class="address-card-body">
                        <div class="address-card-icon">
                            <i class="fas fa-location-dot"></i>
                        </div>

                        <p class="address-province">{{ $address->province }}</p>
                        <p class="address-subtitle">
                            {{ $address->canton }} &mdash; {{ $address->district }} &mdash; {{ $address->zipcode }}
                        </p>

                        <p class="address-detail">{{ $address->detail }}</p>

                        <div class="address-actions">
                            <button class="address-edit-btn"
                                onclick='openEditModal(@json($address))'
                                data-bs-toggle="modal"
                                data-bs-target="#addressModal">
                                Editar
                            </button>

                            <form method="POST" action="{{ route('location.destroy', $address->idlocation) }}" style="flex:1; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="address-delete-btn" style="width:100%;"
                                    onclick="openDeleteConfirm(this.closest('form'))">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="location-empty">
                <i class="fas fa-location-dot"></i>
                <p>No tienes direcciones registradas</p>
            </div>
        @endforelse
    </div>
</div>


{{-- Modal de confirmación: eliminar --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content" style="border-radius: 0; border: none;">
            <div class="confirm-modal-body">
                <i class="fas fa-triangle-exclamation confirm-modal-icon danger"></i>
                <p class="confirm-modal-title">¿Eliminar dirección?</p>
                <p class="confirm-modal-subtitle">Esta acción no se puede deshacer.</p>
            </div>
            <div class="confirm-modal-footer">
                <button type="button" class="confirm-cancel-btn" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="confirm-action-btn danger" id="confirmDeleteBtn">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación: editar --}}
<div class="modal fade" id="editConfirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content" style="border-radius: 0; border: none;">
            <div class="confirm-modal-body">
                <i class="fas fa-pen-to-square confirm-modal-icon neutral"></i>
                <p class="confirm-modal-title">¿Guardar cambios?</p>
                <p class="confirm-modal-subtitle">Se actualizará la dirección seleccionada.</p>
            </div>
            <div class="confirm-modal-footer">
                <button type="button" class="confirm-cancel-btn" id="cancelEditConfirmBtn">Volver</button>
                <button type="button" class="confirm-action-btn" id="confirmEditBtn">Sí, guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal para agregar o editar una dirección --}}
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 0; border: none;">

            <div class="modal-header" style="border-bottom: 1px solid #e0e0e0;">
                <h5 class="modal-title" id="addressModalTitle"
                    style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    Agregar dirección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addressForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="addressFormMethod" value="POST">

                <div class="modal-body" style="padding: 30px;">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 500;">
                                Provincia
                            </label>
                            <input type="text" name="province" id="province" class="form-control"
                                style="border-radius: 0; border: none; border-bottom: 1px solid #e0e0e0; padding: 10px 0; font-size: 14px; background: transparent; box-shadow: none;"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 500;">
                                Cantón
                            </label>
                            <input type="text" name="canton" id="canton" class="form-control"
                                style="border-radius: 0; border: none; border-bottom: 1px solid #e0e0e0; padding: 10px 0; font-size: 14px; background: transparent; box-shadow: none;"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 500;">
                                Distrito
                            </label>
                            <input type="text" name="district" id="district" class="form-control"
                                style="border-radius: 0; border: none; border-bottom: 1px solid #e0e0e0; padding: 10px 0; font-size: 14px; background: transparent; box-shadow: none;"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 500;">
                                Código Postal
                            </label>
                            <input type="text" name="zipcode" id="zipcode" maxlength="5" class="form-control"
                                style="border-radius: 0; border: none; border-bottom: 1px solid #e0e0e0; padding: 10px 0; font-size: 14px; background: transparent; box-shadow: none;"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 500;">
                                Dirección exacta
                            </label>
                            <textarea name="detail" id="detail" class="form-control" rows="2"
                                style="border-radius: 0; border: none; border-bottom: 1px solid #e0e0e0; padding: 10px 0; font-size: 14px; background: transparent; box-shadow: none; resize: none;"
                                required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 20px 30px;">
                    <button type="button"
                        style="background: transparent; border: 1px solid #ccc; padding: 10px 25px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; color: #333;"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="addressSubmitBtn"
                        style="background: #000; color: white; border: none; padding: 10px 25px; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; cursor: pointer;">
                        Guardar dirección
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<script>
    let isEditMode = false;
    let skipEditConfirm = false;
    let pendingDeleteForm = null;

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-ocultar alertas
        document.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function () { if (alert.parentNode) alert.remove(); }, 500);
            }, 3000);
        });

        // Interceptar submit del form de dirección
        document.getElementById('addressForm').addEventListener('submit', function (e) {
            if (isEditMode && !skipEditConfirm) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
                setTimeout(function () {
                    new bootstrap.Modal(document.getElementById('editConfirmModal')).show();
                }, 300);
            }
            skipEditConfirm = false;
        });

        // Confirmar edición
        document.getElementById('confirmEditBtn').addEventListener('click', function () {
            skipEditConfirm = true;
            bootstrap.Modal.getInstance(document.getElementById('editConfirmModal')).hide();
            setTimeout(function () {
                document.getElementById('addressForm').submit();
            }, 300);
        });

        // Volver al modal de edición desde confirmación
        document.getElementById('cancelEditConfirmBtn').addEventListener('click', function () {
            bootstrap.Modal.getInstance(document.getElementById('editConfirmModal')).hide();
            setTimeout(function () {
                new bootstrap.Modal(document.getElementById('addressModal')).show();
            }, 300);
        });

        // Confirmar eliminación
        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
            setTimeout(function () {
                if (pendingDeleteForm) pendingDeleteForm.submit();
            }, 300);
        });
    });

    function openDeleteConfirm(form) {
        pendingDeleteForm = form;
        new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
    }

    function openCreateModal() {
        isEditMode = false;
        document.getElementById('addressModalTitle').innerText = 'Agregar dirección';
        document.getElementById('addressSubmitBtn').innerText = 'Guardar dirección';

        const form = document.getElementById('addressForm');
        form.action = "{{ route('location.store') }}";
        document.getElementById('addressFormMethod').value = 'POST';
        form.reset();
    }

    function openEditModal(address) {
        isEditMode = true;
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
