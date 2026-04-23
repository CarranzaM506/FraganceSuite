<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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

    public function success($id)
    {
        $order = Order::find($id);

        return view('order.success', compact('order'));
    }
}
