@extends('partsAdmin.header')

@section('title', 'Dashboard')

@section('content')
    <main class="content-wrap">

        {{-- Tarjetas de resumen --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="dashboard-stat-card">
                    <div class="stat-label">Ventas {{ $currentYear }}</div>
                    <div class="stat-value">₡{{ number_format($totalVentasAnio, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dashboard-stat-card">
                    <div class="stat-label">Pedidos {{ $currentYear }}</div>
                    <div class="stat-value">{{ number_format($totalPedidosAnio) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dashboard-stat-card">
                    <div class="stat-label">Ventas este mes</div>
                    <div class="stat-value">₡{{ number_format($totalVentasMes, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dashboard-stat-card">
                    <div class="stat-label">Pedidos este mes</div>
                    <div class="stat-value">{{ number_format($totalPedidosMes) }}</div>
                </div>
            </div>
        </div>

        {{-- Gráfico ventas mensuales --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Ventas mensuales — {{ $currentYear }}</span>
                <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">Ver detalle</a>
            </div>
            <div class="card-body">
                <div style="position:relative; height:280px;">
                    <canvas id="chartMensual"></canvas>
                </div>
            </div>
        </div>

        {{-- Gráfico ventas diarias --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Ventas diarias — últimos 30 días</span>
                <a href="{{ route('dailySales') }}" class="btn btn-dark btn-sm">Ver detalle</a>
            </div>
            <div class="card-body">
                <div style="position:relative; height:280px;">
                    <canvas id="chartDiario"></canvas>
                </div>
            </div>
        </div>

        {{-- ========== MÁS VENDIDOS DEL MES ========== --}}
        @if(isset($bestSellers) && $bestSellers->count() > 0)
        <div class="card">
            <div class="card-header">
                <span>MÁS VENDIDOS DEL MES</span>
            </div>
            <div class="card-body">
                <div class="admin-bestsellers-grid {{ $bestSellers->count() == 1 ? 'single-product' : '' }}">
                    @foreach($bestSellers as $product)
                    <div class="admin-bestseller-card">
                        <div class="admin-bestseller-image">
                            @if($product->pathimg)
                                <img src="{{ $product->pathimg }}" alt="{{ $product->name }}">
                            @else
                                <div class="image-placeholder">
                                    <i class="fas fa-wine-bottle"></i>
                                </div>
                            @endif
                        </div>
                        <div class="admin-bestseller-info">
                            <h4>{{ $product->name }}</h4>
                            <p class="brand">{{ $product->brand }}</p>
                            <p class="price">₡{{ number_format($product->price, 2) }}</p>
                            <p class="sold-count">📦 {{ $product->total_sold }} unidades vendidas</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </main>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const gridColor  = '#e5e5e5';
        const textColor  = '#4a4a4a';
        const barColor   = '#1a1a1a';
        const barHover   = '#333333';

        const sharedOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₡' + ctx.parsed.y.toLocaleString('es-CR', { minimumFractionDigits: 0 })
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { size: 11 } }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: val => '₡' + val.toLocaleString('es-CR', { minimumFractionDigits: 0 })
                    }
                }
            }
        };

        new Chart(document.getElementById('chartMensual'), {
            type: 'bar',
            data: {
                labels: @json($meses),
                datasets: [{
                    data: @json($monthlySales),
                    backgroundColor: barColor,
                    hoverBackgroundColor: barHover,
                    borderWidth: 0,
                    borderRadius: 0
                }]
            },
            options: sharedOptions
        });

        new Chart(document.getElementById('chartDiario'), {
            type: 'bar',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    data: @json($dailySales),
                    backgroundColor: barColor,
                    hoverBackgroundColor: barHover,
                    borderWidth: 0,
                    borderRadius: 0
                }]
            },
            options: sharedOptions
        });
    </script>
@endsection