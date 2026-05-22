@extends('partsAdmin.header')

@section('title', 'Seguimiento de Envíos')

@section('content')

@php
    $statusLabels = [
        'pending'     => 'Pendiente',
        'in_progress' => 'En progreso',
        'completed'   => 'Finalizado',
    ];
@endphp

<style>
.guide-display { cursor: pointer; }
.guide-hint { opacity: 0.45; font-size: 0.75rem; transition: opacity .15s; }
.guide-display:hover .guide-hint { opacity: 0.9; }

.row-status-select.status-pending     { background-color: #fff8e1; border-color: #ffc107; }
.row-status-select.status-in_progress { background-color: #e3f2fd; border-color: #2196f3; }
.row-status-select.status-completed   { background-color: #e8f5e9; border-color: #43a047; }

@media (max-width: 767px) {
    .shipments-filters { flex-direction: column !important; gap: .75rem !important; }
    .shipments-filters > div { width: 100%; }
    .shipments-filters .input-group { width: 100%; }
}
</style>

<div class="container-fluid px-3 px-md-4 mt-4">

    {{-- Título --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0 fw-bold">Seguimiento de Envíos</h4>
        <span class="badge bg-dark">
            {{ $orders->count() }} resultado{{ $orders->count() !== 1 ? 's' : '' }}
        </span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros (todos a la izquierda) --}}
    <div class="d-flex flex-wrap align-items-end gap-3 mb-4 shipments-filters">

        <div>
            <label class="form-label form-label-sm fw-semibold mb-1" for="statusFilter">Estado</label>
            <select id="statusFilter" class="form-select form-select-sm" style="min-width:170px;">
                @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                        {{ $label }}{{ isset($counts[$key]) && $counts[$key] > 0 ? ' (' . $counts[$key] . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label form-label-sm fw-semibold mb-1" for="searchFilter">Buscar cliente</label>
            <div class="input-group input-group-sm" style="min-width:240px;">
                <input type="text" id="searchFilter" class="form-control"
                       value="{{ $search }}"
                       placeholder="Nombre, email o teléfono...">
                <button class="btn btn-dark" id="searchBtn" type="button">Buscar</button>
            </div>
        </div>

        @if($search)
            <div class="align-self-end">
                <a href="{{ route('pendingShipments', ['status' => $status]) }}"
                   class="btn btn-sm btn-outline-secondary">&#10005; Limpiar</a>
            </div>
        @endif

    </div>

    {{-- ────────────── TABLA DESKTOP ────────────── --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width:80px;"># Pedido</th>
                    <th style="width:90px;">Fecha</th>
                    <th>Cliente</th>
                    <th>Destino</th>
                    <th style="width:110px;">Total</th>
                    <th style="width:210px;">Guía de envío</th>
                    <th style="width:165px;">Estado</th>
                    <th style="width:46px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php $shStatus = $order->shipping_status ?? 'pending'; @endphp
                    <tr>
                        <td class="text-center fw-semibold">#{{ $order->idorder }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-medium">{{ $order->user?->name }} {{ $order->user?->lastname }}</div>
                            <small class="text-muted">{{ $order->user?->email }}</small>
                        </td>
                        <td>{{ $order->location?->province }}, {{ $order->location?->canton }}</td>
                        <td class="text-center">&#8353;{{ number_format($order->total, 2) }}</td>

                        {{-- Guía: edición inline --}}
                        <td class="guide-cell text-center p-2" data-order-id="{{ $order->idorder }}">
                            <div class="guide-display px-1" onclick="enableGuideEdit(this)">
                                @if($order->guidenumber)
                                    <code class="text-dark">{{ $order->guidenumber }}</code>
                                    <span class="guide-hint ms-1">&#9998;</span>
                                @else
                                    <span class="text-muted fst-italic small">Clic para asignar</span>
                                @endif
                            </div>
                            <div class="guide-edit d-none">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="guide-input form-control"
                                           value="{{ $order->guidenumber ?? '' }}"
                                           placeholder="N&#730; gu&#237;a"
                                           onkeydown="handleGuideKey(event,{{ $order->idorder }})">
                                    <button class="btn btn-dark guide-save-btn"
                                            onclick="saveGuide(this,{{ $order->idorder }})"
                                            title="Guardar">&#10003;</button>
                                    <button class="btn btn-outline-secondary guide-cancel-btn"
                                            onclick="cancelGuideEdit(this)"
                                            title="Cancelar">&#10005;</button>
                                </div>
                            </div>
                        </td>

                        {{-- Estado: select inline --}}
                        <td class="text-center p-2">
                            <select class="form-select form-select-sm row-status-select status-{{ $shStatus }}"
                                    data-order-id="{{ $order->idorder }}"
                                    data-current-value="{{ $shStatus }}"
                                    onchange="handleStatusChange(this)">
                                <option value="pending"     {{ $shStatus==='pending'     ? 'selected':'' }}>Pendiente</option>
                                <option value="in_progress" {{ $shStatus==='in_progress' ? 'selected':'' }}>En progreso</option>
                                <option value="completed"   {{ $shStatus==='completed'   ? 'selected':'' }}>Finalizado</option>
                            </select>
                        </td>

                        {{-- Expandir --}}
                        <td class="text-center p-2">
                            <button class="btn btn-sm btn-outline-secondary pending-toggle"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#details-{{ $order->idorder }}"
                                    aria-expanded="false">&#9660;</button>
                        </td>
                    </tr>

                    {{-- Fila de detalle expandible --}}
                    <tr>
                        <td colspan="8" class="p-0 border-top-0">
                            <div class="collapse" id="details-{{ $order->idorder }}">
                                <div class="p-3 text-start" style="background:var(--aroma-gray-100);">
                                    <p class="mb-2 small">
                                        <strong>Direcci&#243;n:</strong>
                                        {{ $order->location?->province }},
                                        {{ $order->location?->canton }},
                                        {{ $order->location?->district }},
                                        {{ $order->location?->detail }}
                                        @if($order->location?->zipcode)
                                            &nbsp;(CP:&nbsp;{{ $order->location->zipcode }})
                                        @endif
                                    </p>
                                    <p class="mb-2 small"><strong>M&#233;todo de pago:</strong> {{ $order->purchasemethod }}</p>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th class="text-start">Producto</th>
                                                <th>Cant.</th>
                                                <th>Precio unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->details as $detail)
                                                <tr>
                                                    <td class="text-start">{{ $detail->product?->name ?? 'Producto eliminado' }}</td>
                                                    <td class="text-center">{{ $detail->quantity }}</td>
                                                    <td class="text-center">&#8353;{{ number_format($detail->price, 2) }}</td>
                                                    <td class="text-center">&#8353;{{ number_format($detail->price * $detail->quantity, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <strong>No hay pedidos en estado &#171;{{ $statusLabels[$status] ?? $status }}&#187;.</strong><br>
                            <small class="text-muted">
                                @if($search)
                                    Sin resultados para &#171;{{ $search }}&#187;.
                                    <a href="{{ route('pendingShipments', ['status' => $status]) }}">Limpiar b&#250;squeda</a>
                                @else
                                    Todos los pedidos han sido procesados.
                                @endif
                            </small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ────────────── CARDS MOBILE ────────────── --}}
    <div class="d-md-none">
        <div class="mb-3">
            <input type="text" id="mobileSearch" class="form-control form-control-sm"
                   value="{{ $search }}"
                   placeholder="Buscar por nombre o pedido...">
        </div>

        <div id="pendingCards">
            @forelse($orders as $order)
                @php $shStatus = $order->shipping_status ?? 'pending'; @endphp
                <article class="mobile-record-card"
                         data-search="{{ strtolower('#'.$order->idorder.' '.$order->user?->name.' '.$order->user?->lastname.' '.$order->user?->email.' '.$order->location?->province.' '.$order->location?->canton) }}">

                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mobile-record-title mb-0">#{{ $order->idorder }}</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</small>
                    </div>

                    <p class="mobile-record-meta"><strong>Cliente:</strong> {{ $order->user?->name }} {{ $order->user?->lastname }}</p>
                    <p class="mobile-record-meta"><strong>Destino:</strong> {{ $order->location?->province }}, {{ $order->location?->canton }}</p>
                    <p class="mobile-record-meta"><strong>Total:</strong> &#8353;{{ number_format($order->total, 2) }}</p>

                    {{-- Guía: inline editable --}}
                    <div class="mb-2">
                        <strong class="small">Gu&#237;a de env&#237;o</strong>
                        <div class="guide-cell mt-1" data-order-id="{{ $order->idorder }}">
                            <div class="guide-display" onclick="enableGuideEdit(this)">
                                @if($order->guidenumber)
                                    <code class="text-dark">{{ $order->guidenumber }}</code>
                                    <span class="guide-hint ms-1">&#9998;</span>
                                @else
                                    <span class="text-muted fst-italic small">Toca para asignar</span>
                                @endif
                            </div>
                            <div class="guide-edit d-none mt-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="guide-input form-control"
                                           value="{{ $order->guidenumber ?? '' }}"
                                           placeholder="N&#730; gu&#237;a"
                                           onkeydown="handleGuideKey(event,{{ $order->idorder }})">
                                    <button class="btn btn-dark guide-save-btn"
                                            onclick="saveGuide(this,{{ $order->idorder }})">&#10003;</button>
                                    <button class="btn btn-outline-secondary guide-cancel-btn"
                                            onclick="cancelGuideEdit(this)">&#10005;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estado: select inline --}}
                    <div class="mb-2">
                        <strong class="small">Estado</strong>
                        <select class="form-select form-select-sm row-status-select status-{{ $shStatus }} mt-1"
                                data-order-id="{{ $order->idorder }}"
                                data-current-value="{{ $shStatus }}"
                                onchange="handleStatusChange(this)">
                            <option value="pending"     {{ $shStatus==='pending'     ? 'selected':'' }}>Pendiente</option>
                            <option value="in_progress" {{ $shStatus==='in_progress' ? 'selected':'' }}>En progreso</option>
                            <option value="completed"   {{ $shStatus==='completed'   ? 'selected':'' }}>Finalizado</option>
                        </select>
                    </div>

                    {{-- Expandir productos --}}
                    <button class="btn btn-sm btn-outline-secondary w-100 mobile-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#mob-det-{{ $order->idorder }}">
                        Ver productos &#9660;
                    </button>
                    <div class="collapse mt-2" id="mob-det-{{ $order->idorder }}">
                        <div class="p-2" style="background:var(--aroma-gray-100);">
                            <p class="small mb-1">
                                <strong>Direcci&#243;n:</strong>
                                {{ $order->location?->province }}, {{ $order->location?->canton }},
                                {{ $order->location?->district }}, {{ $order->location?->detail }}
                            </p>
                            <p class="small mb-2"><strong>M&#233;todo:</strong> {{ $order->purchasemethod }}</p>
                            @foreach($order->details as $detail)
                                <div class="d-flex justify-content-between small py-1 border-bottom">
                                    <span>{{ $detail->product?->name ?? 'Producto eliminado' }} &times;{{ $detail->quantity }}</span>
                                    <span>&#8353;{{ number_format($detail->price * $detail->quantity, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </article>
            @empty
                <div class="text-center py-5">
                    <strong>No hay pedidos en estado &#171;{{ $statusLabels[$status] ?? $status }}&#187;.</strong>
                    @if($search)<br><small class="text-muted">Sin resultados para &#171;{{ $search }}&#187;.</small>@endif
                </div>
            @endforelse
        </div>

        <div class="mobile-records-pagination mt-3" id="pendingPagination"></div>
    </div>

    {{-- ────────────── FORMULARIOS OCULTOS (uno por pedido) ────────────── --}}
    @foreach($orders as $order)
        <form id="row-form-{{ $order->idorder }}"
              method="POST"
              action="{{ route('pendingShipments.update', $order->idorder) }}"
              class="d-none">
            @csrf
            @method('PATCH')
            <input type="hidden" name="return_status" value="{{ $status }}">
            <input type="hidden" name="return_search"  value="{{ $search }}">
            <input type="hidden" name="shipping_status" id="field-status-{{ $order->idorder }}" value="{{ $order->shipping_status ?? 'pending' }}">
            <input type="hidden" name="guidenumber"     id="field-guide-{{ $order->idorder }}"  value="{{ $order->guidenumber ?? '' }}">
        </form>
    @endforeach

</div>
@endsection

@section('scripts')
<script>
// ── Filtros de página ────────────────────────────────────────────────────
var PENDING_BASE = @json(route('pendingShipments'));

function applyFilters() {
    var status = document.getElementById('statusFilter').value;
    var search = document.getElementById('searchFilter').value.trim();
    var url    = new URL(PENDING_BASE, window.location.origin);
    url.searchParams.set('status', status);
    if (search) url.searchParams.set('search', search);
    window.location.href = url.toString();
}

document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('searchBtn').addEventListener('click', applyFilters);
document.getElementById('searchFilter').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') applyFilters();
});

// ── Guía: edición inline ─────────────────────────────────────────────────
function enableGuideEdit(displayEl) {
    var cell  = displayEl.closest('.guide-cell');
    cell.querySelector('.guide-display').classList.add('d-none');
    var edit  = cell.querySelector('.guide-edit');
    edit.classList.remove('d-none');
    var input = edit.querySelector('.guide-input');
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
}

function cancelGuideEdit(btnEl) {
    var cell    = btnEl.closest('.guide-cell');
    var orderId = cell.dataset.orderId;
    cell.querySelector('.guide-input').value = document.getElementById('field-guide-' + orderId).value;
    cell.querySelector('.guide-display').classList.remove('d-none');
    cell.querySelector('.guide-edit').classList.add('d-none');
}

function saveGuide(btnEl, orderId) {
    var cell     = btnEl.closest('.guide-cell');
    var newGuide = cell.querySelector('.guide-input').value.trim();
    var curStatus = document.getElementById('field-status-' + orderId).value;
    var willAuto  = newGuide !== '' && curStatus === 'pending';

    var msg;
    if (!newGuide) {
        msg = '¿Eliminar la guía de envío del pedido <strong>#' + orderId + '</strong>?';
    } else if (willAuto) {
        msg = '¿Asignar guía <strong>' + escHtml(newGuide) + '</strong> y pasar el pedido <strong>#' + orderId + '</strong> a <strong>En progreso</strong>?';
    } else {
        msg = '¿Guardar guía <strong>' + escHtml(newGuide) + '</strong> para el pedido <strong>#' + orderId + '</strong>?';
    }

    openAdminUpdateConfirm(msg, function () {
        document.getElementById('field-guide-' + orderId).value = newGuide;

        if (willAuto) {
            setStatusValue(orderId, 'in_progress');
        }

        document.getElementById('row-form-' + orderId).submit();
    });
}

function handleGuideKey(e, orderId) {
    if (e.key === 'Enter') {
        e.preventDefault();
        var cell = e.target.closest('.guide-cell');
        saveGuide(cell.querySelector('.guide-save-btn'), orderId);
    } else if (e.key === 'Escape') {
        cancelGuideEdit(e.target.closest('.guide-cell').querySelector('.guide-cancel-btn'));
    }
}

// ── Estado: select inline ────────────────────────────────────────────────
var STATUS_LABELS = {
    pending:     'Pendiente',
    in_progress: 'En progreso',
    completed:   'Finalizado / Cerrado'
};

function handleStatusChange(selectEl) {
    var orderId   = selectEl.dataset.orderId;
    var newStatus = selectEl.value;
    var prevValue = selectEl.dataset.currentValue;
    var confirmed = false;

    openAdminUpdateConfirm(
        '¿Cambiar estado del pedido <strong>#' + orderId + '</strong> a <strong>' + STATUS_LABELS[newStatus] + '</strong>?',
        function () {
            confirmed = true;
            setStatusValue(orderId, newStatus);
            document.getElementById('row-form-' + orderId).submit();
        }
    );

    // Revertir si el usuario cancela
    document.getElementById('adminUpdateConfirmModal')
        .addEventListener('hidden.bs.modal', function () {
            if (!confirmed) {
                selectEl.value = prevValue;
            }
        }, { once: true });
}

// Actualiza el hidden input Y todos los selects visibles del pedido
function setStatusValue(orderId, newStatus) {
    document.getElementById('field-status-' + orderId).value = newStatus;
    document.querySelectorAll('.row-status-select[data-order-id="' + orderId + '"]')
        .forEach(function (sel) {
            sel.value = newStatus;
            sel.dataset.currentValue = newStatus;
            sel.className = sel.className.replace(/\bstatus-\S+/g, '').trim();
            sel.classList.add('status-' + newStatus);
        });
}

// ── Collapse chevron ─────────────────────────────────────────────────────
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
    var target = document.querySelector(btn.dataset.bsTarget);
    if (!target) return;
    target.addEventListener('show.bs.collapse', function () {
        if (btn.classList.contains('pending-toggle'))  btn.textContent = '▲';
        if (btn.classList.contains('mobile-toggle'))   btn.textContent = 'Ver productos ▲';
    });
    target.addEventListener('hide.bs.collapse', function () {
        if (btn.classList.contains('pending-toggle'))  btn.textContent = '▼';
        if (btn.classList.contains('mobile-toggle'))   btn.textContent = 'Ver productos ▼';
    });
});

// ── Búsqueda mobile (client-side) ────────────────────────────────────────
(function () {
    var input      = document.getElementById('mobileSearch');
    var container  = document.getElementById('pendingCards');
    var pagination = document.getElementById('pendingPagination');
    if (!input || !container) return;

    var all      = Array.from(container.querySelectorAll('.mobile-record-card'));
    var filtered = all;
    var pageSize = 10;
    var page     = 1;

    function render() {
        var total = Math.max(1, Math.ceil(filtered.length / pageSize));
        if (page > total) page = total;
        var start = (page - 1) * pageSize;
        all.forEach(function (c) { c.style.display = 'none'; });
        filtered.slice(start, start + pageSize).forEach(function (c) { c.style.display = ''; });
        pagination.innerHTML = '';
        if (total <= 1) return;
        var prev = document.createElement('button');
        prev.className = 'btn btn-outline-secondary btn-sm';
        prev.textContent = '←';
        prev.disabled = page === 1;
        prev.onclick = function () { if (page > 1) { page--; render(); } };
        var lbl = document.createElement('span');
        lbl.className = 'mobile-page-label';
        lbl.textContent = 'Página ' + page + ' de ' + total;
        var next = document.createElement('button');
        next.className = 'btn btn-outline-secondary btn-sm';
        next.textContent = '→';
        next.disabled = page === total;
        next.onclick = function () { if (page < total) { page++; render(); } };
        pagination.append(prev, lbl, next);
    }

    input.addEventListener('input', function () {
        var term = input.value.trim().toLowerCase();
        filtered = all.filter(function (c) { return (c.dataset.search || '').includes(term); });
        page = 1;
        render();
    });

    render();
})();

// ── Helper ───────────────────────────────────────────────────────────────
function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>
@endsection
