@extends('partsAdmin.header')

@section('title', 'Agregar codigo promocion')

@section('content')

    <div class="container-fluid py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-6">

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <h3 class="mb-1">Crear codigo promocional</h3>
                                <p class="text-muted mb-0">Ingrese los datos del codigo de descuento.</p>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('promotionCode.index') }}" class="btn btn-outline-secondary">
                                    Ver codigos
                                </a>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    Volver
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('promotionCode.store') }}">
                            @csrf

                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Codigo de promocion</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Porcentaje de descuento (%)</label>
                                    <input type="number" step="0.01" min="0" name="value" class="form-control"
                                        value="{{ old('value') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Fecha de inicio</label>
                                    <input type="date" name="startdate" class="form-control"
                                        value="{{ old('startdate') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Fecha de fin</label>
                                    <input type="date" name="enddate" class="form-control" value="{{ old('enddate') }}">
                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 flex-wrap">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-dark px-4">
                                    Guardar
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection