<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

        return view('dashboard.main', compact(
            'meses', 'monthlySales',
            'dailyLabels', 'dailySales',
            'totalVentasAnio', 'totalPedidosAnio',
            'totalVentasMes', 'totalPedidosMes',
            'currentYear'
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

    public function create() {}

    public function store(Request $request) {}

    public function show(string $id) {}

    public function edit(string $id) {}

    public function update(Request $request, string $id) {}

    public function destroy(string $id) {}

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
