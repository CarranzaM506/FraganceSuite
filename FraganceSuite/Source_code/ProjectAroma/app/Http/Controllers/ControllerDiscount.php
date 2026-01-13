<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Discount;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ControllerDiscount extends Controller
{
    public function index()
    {
        $discounts = Discount::all();
        return view('dashboard.discount.index', compact('discounts'));
    }

    public function create()
    {
        // For scalability we will load products via AJAX. Still provide lists for filters.
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand');
        return view('dashboard.discount.create', compact('categories','brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.01',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'condition' => 'required|string|max:100',
            'products' => 'required|array|min:1',
            'products.*' => 'integer|exists:product,idproduct',
        ],[
            'value.required' => 'El valor del descuento es obligatorio.',
            'startdate.required' => 'La fecha de inicio es obligatoria.',
            'enddate.required' => 'La fecha límite es obligatoria.',
            'condition.required' => 'La condición de la promoción es obligatoria.',
            'products.required' => 'Debes seleccionar al menos un producto para la promoción.',
        ]);

        // Check for conflicts: products that already have a discount
        $selected = $request->products;
        $conflicts = Product::whereIn('idproduct', $selected)->whereNotNull('iddiscount')->get();

        if ($conflicts->isNotEmpty() && !$request->boolean('force_replace')) {
            // return to form with conflict info so user can confirm replacement
            return back()->withInput()->with('conflicts', $conflicts->map(function($p){
                return ['id' => $p->idproduct, 'name' => $p->name, 'brand' => $p->brand];
            }));
        }

        // Create discount
        $d = Discount::create([
            'value' => $request->value,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'condition' => $request->condition,
        ]);

        // Assign products to this discount (overwrite previous if force_replace)
        foreach ($selected as $pid) {
            $p = Product::find($pid);
            if ($p) {
                $p->iddiscount = $d->iddiscount;
                $p->save();
            }
        }

        return redirect()->route('discount.index')->with('success', 'Promoción creada exitosamente.');
    }

    public function show($id)
    {
        // not used
    }

    public function edit($id)
    {
        $discount = Discount::findOrFail($id);
        $selected = Product::where('iddiscount', $id)->pluck('idproduct')->toArray();
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand');
        return view('dashboard.discount.edit', compact('discount','selected','categories','brands'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.01',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'condition' => 'required|string|max:100',
            'products' => 'required|array|min:1',
            'products.*' => 'integer|exists:product,idproduct',
        ],[
            'value.required' => 'El valor del descuento es obligatorio.',
            'startdate.required' => 'La fecha de inicio es obligatoria.',
            'enddate.required' => 'La fecha límite es obligatoria.',
            'condition.required' => 'La condición de la promoción es obligatoria.',
            'products.required' => 'Debes seleccionar al menos un producto para la promoción.',
        ]);

        $discount = Discount::findOrFail($id);
        $discount->value = $request->value;
        $discount->startdate = $request->startdate;
        $discount->enddate = $request->enddate;
        $discount->condition = $request->condition;
        $discount->save();

        $selected = $request->products;

        // Check for conflicts with other discounts (products that have different discount)
        $conflicts = Product::whereIn('idproduct', $selected)->whereNotNull('iddiscount')->where('iddiscount','<>',$id)->get();
        if ($conflicts->isNotEmpty() && !$request->boolean('force_replace')) {
            return back()->withInput()->with('conflicts', $conflicts->map(function($p){
                return ['id' => $p->idproduct, 'name' => $p->name, 'brand' => $p->brand];
            }));
        }

        // Detach previous products that are no longer selected
        Product::where('iddiscount', $id)->whereNotIn('idproduct', $selected)->update(['iddiscount' => null]);

        // Attach selected products to this discount
        foreach ($selected as $pid) {
            $p = Product::find($pid);
            if ($p) {
                $p->iddiscount = $id;
                $p->save();
            }
        }

        return redirect()->route('discount.index')->with('success', 'Promoción actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        // Remove discount from products
        Product::where('iddiscount', $id)->update(['iddiscount' => null]);
        $discount->delete();
        return redirect()->route('discount.index')->with('success', 'Promoción eliminada exitosamente.');
    }

    // Return products for a discount (JSON) used by modal in index
    public function products($id)
    {
        $discount = Discount::findOrFail($id);
        $products = Product::where('iddiscount', $id)->get();
        $data = $products->map(function($p) use ($discount) {
            $old = $p->price;
            $new = $old * (1 - ($discount->value / 100));
            return [
                'id' => $p->idproduct,
                'name' => $p->name,
                'brand' => $p->brand,
                'pathimg' => $p->pathimg,
                'old_price' => number_format($old, 2),
                'new_price' => number_format($new, 2),
            ];
        });

        return response()->json(['discount' => $discount, 'products' => $data]);
    }

    // AJAX search endpoint for products used in create/edit promotion pages
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');
        $categories = $request->query('categories', []);
        $brands = $request->query('brands', []);

        $query = Product::query();

        if ($q) {
            $q = strtolower($q);
            $query->where(function($sub) use ($q) {
                $sub->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"]) ->orWhereRaw('LOWER(brand) LIKE ?', ["%{$q}%"]) ->orWhereRaw('LOWER(category) LIKE ?', ["%{$q}%"]);
            });
        }

        if (!empty($categories)) {
            $query->whereIn('category', $categories);
        }

        if (!empty($brands)) {
            $query->whereIn('brand', $brands);
        }

        $products = $query->limit(50)->get();

        $data = $products->map(function($p) {
            return [
                'id' => $p->idproduct,
                'name' => $p->name,
                'brand' => $p->brand,
                'category' => $p->category,
                'pathimg' => $p->pathimg,
                'price' => number_format($p->price,2),
                'iddiscount' => $p->iddiscount,
            ];
        });

        return response()->json(['products' => $data]);
    }
}
