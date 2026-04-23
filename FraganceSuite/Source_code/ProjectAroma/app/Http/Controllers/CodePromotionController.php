<?php

namespace App\Http\Controllers;

use App\Models\CodePromotion;
use App\Models\Product;
use Illuminate\Support\Facades\Log; 

use Illuminate\Http\Request;

class CodePromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $codes = CodePromotion::orderByDesc('idcode_promotion')->get();
        return view('dashboard.discount.indexCode', compact('codes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.discount.createCode');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|unique:code_promotion,code_promotion|max:255',
            'value' => 'required|numeric|min:0|max:100',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after:startdate',
        ], [
            'code.required' => 'El código de promoción es obligatorio.',
            'code.unique' => 'Este código ya existe.',
            'value.required' => 'El porcentaje de descuento es obligatorio.',
            'value.numeric' => 'El porcentaje debe ser un número.',
            'value.max' => 'El porcentaje no puede ser mayor a 100.',
            'enddate.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        $codePromotion = new CodePromotion();
        $codePromotion->code_promotion = $validatedData['code'];
        $codePromotion->value = $validatedData['value'];
        $codePromotion->startdate = $validatedData['startdate'];
        $codePromotion->enddate = $validatedData['enddate'];
        $codePromotion->save();

        return redirect()->route('promotionCode.create')
            ->with('success', 'Código de promoción creado exitosamente.');
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

 
 
public function destroy($id)
{
    try {
        $codePromotion = CodePromotion::findOrFail($id);
        $codePromotion->delete();
        
        return redirect()->route('promotionCode.index')
            ->with('success', 'Código de promoción eliminado exitosamente.');
    } catch (\Exception $e) {
        return redirect()->route('promotionCode.index')
            ->with('error', 'Error al eliminar el código de promoción.');
    }
}
}
