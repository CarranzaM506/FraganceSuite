<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function dashboard()
    {
        $currentYear = now()->year;

        $monthlySalesRaw = Order::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(total) as totalSales'),
            DB::raw('COUNT(*) as totalOrders')
        )
            ->where('state', 1)
            ->whereYear('date', $currentYear)
            ->groupBy(DB::raw('MONTH(date)'))
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthlySales = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlySales[] = isset($monthlySalesRaw[$i]) ? (float) $monthlySalesRaw[$i]->totalSales : 0;
        }

        $dailySalesRaw = Order::select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(total) as totalSales'),
            DB::raw('COUNT(*) as totalOrders')
        )
            ->where('state', 1)
            ->where('date', '>=', now()->subDays(29)->startOfDay())
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day', 'asc')
            ->get()
            ->keyBy('day');

        $dailyLabels = [];
        $dailySales = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[] = now()->subDays($i)->format('d/m');
            $dailySales[] = isset($dailySalesRaw[$date]) ? (float) $dailySalesRaw[$date]->totalSales : 0;
        }

        $totalVentasAnio  = (float) Order::where('state', 1)->whereYear('date', $currentYear)->sum('total');
        $totalPedidosAnio = Order::where('state', 1)->whereYear('date', $currentYear)->count();
        $totalVentasMes   = (float) Order::where('state', 1)->whereYear('date', $currentYear)->whereMonth('date', now()->month)->sum('total');
        $totalPedidosMes  = Order::where('state', 1)->whereYear('date', $currentYear)->whereMonth('date', now()->month)->count();

        // ========== MÁS VENDIDOS DEL MES ==========
        $bestSellers = collect();
        
        try {
            $bestSellers = Product::select(
                    'product.idproduct',
                    'product.name',
                    'product.price',
                    'product.brand',
                    'product.pathimg',
                    DB::raw('SUM(orderdetail.quantity) as total_sold')
                )
                ->join('orderdetail', 'product.idproduct', '=', 'orderdetail.idproduct')
                ->join('order', 'orderdetail.idorder', '=', 'order.idorder')
                ->whereMonth('order.date', now()->month)
                ->whereYear('order.date', now()->year)
                ->where('order.state', 1)
                ->groupBy(
                    'product.idproduct',
                    'product.name',
                    'product.price',
                    'product.brand',
                    'product.pathimg'
                )
                ->orderByDesc('total_sold')
                ->limit(2)
                ->get();
                
        } catch (\Exception $e) {
            \Log::error('Error en más vendidos dashboard: ' . $e->getMessage());
        }

        return view('dashboard.main', compact(
            'meses', 'monthlySales',
            'dailyLabels', 'dailySales',
            'totalVentasAnio', 'totalPedidosAnio',
            'totalVentasMes', 'totalPedidosMes',
            'currentYear',
            'bestSellers'
        ));
    }

    public function index(Request $request)
    {
        $year = $request->year;

        $ventasMensuales = Order::select(
            DB::raw('YEAR(date) as year'),
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(total) as totalSales'),
            DB::raw('COUNT(*) as totalOrders'),
            DB::raw('AVG(total) as avgOrder')
        )
            ->where('state', 1)
            ->when($year, function ($query) use ($year) {
                $query->whereYear('date', $year);
            })
            ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
            ->orderBy('month', 'asc')
            ->get();

        $years = Order::select(DB::raw('YEAR(date) as year'))
            ->where('state', 1)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('dashboard.sales.monthly-sales', compact('ventasMensuales', 'years'));
    }

    public function dailySales(Request $request)
    {
        $date = $request->date;

        $ventasDiarias = Order::select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(total) as totalSales'),
            DB::raw('COUNT(*) as totalOrders'),
            DB::raw('AVG(total) as avgOrder')
        )
            ->where('state', 1)
            ->when($date, function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day', 'desc')
            ->get();

        return view('dashboard.sales.daily-sales', compact('ventasDiarias'));
    }

    public function ordersByPeriod(Request $request)
    {
        $year  = $request->year;
        $month = $request->month;
        $date  = $request->date;

        $orders = Order::with(['details.product'])
            ->where('state', 1)
            ->when($year && $month, fn($q) => $q->whereYear('date', $year)->whereMonth('date', $month))
            ->when($date, fn($q) => $q->whereDate('date', $date))
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($order) => [
                'idorder'        => $order->idorder,
                'date'           => $order->date,
                'sale_type'      => $order->sale_type ?? 'web',
                'purchasemethod' => $order->purchasemethod,
                'total'          => $order->total,
                'items'          => $order->details->map(fn($d) => [
                    'name'     => optional($d->product)->name ?? 'Producto eliminado',
                    'brand'    => optional($d->product)->brand ?? '',
                    'quantity' => $d->quantity,
                    'price'    => $d->price,
                ]),
            ]);

        return response()->json($orders);
    }

    public function create() {}

    public function store(Request $request) {}

    public function show(string $id) {}

    public function edit(string $id) {}

    public function update(Request $request, string $id) {}

    public function destroy(string $id) {}

    public function pendingShipments(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = trim($request->get('search', ''));

        $validStatuses = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        $query = Order::with(['user', 'location', 'details.product'])
            ->where('state', 1)
            ->where('sale_type', 'web')
            ->where('shipping_status', $status);

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('date', 'asc')->get();

        $counts = Order::where('state', 1)
            ->where('sale_type', 'web')
            ->selectRaw("shipping_status, COUNT(*) as total")
            ->groupBy('shipping_status')
            ->pluck('total', 'shipping_status');

        return view('dashboard.sales.pending-shipments', compact('orders', 'status', 'search', 'counts'));
    }

    public function updateShipment(Request $request, $id)
    {
        $order = Order::where('state', 1)->where('sale_type', 'web')->findOrFail($id);

        $validated = $request->validate([
            'shipping_status' => 'required|in:pending,in_progress,completed',
            'guidenumber'     => 'nullable|string|max:255',
        ]);

        $order->shipping_status = $validated['shipping_status'];
        $order->guidenumber     = $validated['guidenumber'] ?? $order->guidenumber;
        $order->save();

        $returnStatus = $request->get('return_status', $validated['shipping_status']);
        $returnSearch = $request->get('return_search', '');

        return redirect()
            ->route('pendingShipments', array_filter(['status' => $returnStatus, 'search' => $returnSearch]))
            ->with('success', "Pedido #{$order->idorder} actualizado correctamente.");

    public function history()
    {
        $orders = Order::where('iduser', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('profile.orders.index', compact('orders'));

    }

    public function success($id)
    {
        $order = Order::find($id);

        return view('order.success', compact('order'));
    }
}