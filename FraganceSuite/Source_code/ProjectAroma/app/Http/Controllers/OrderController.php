<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->year;

        $ventasMensuales = Order::select(
            DB::raw('YEAR(date) as year'), // 🔥 importante
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function success($id)
    {
        $order = Order::find($id);

        return view('order.success', compact('order'));
    }
}
