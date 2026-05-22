<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InfoCarousel;
use Illuminate\Http\Request;

class InfoCarouselController extends Controller
{
    public function index()
    {
        $items = InfoCarousel::orderBy('order_position', 'asc')->get();
        return view('dashboard.info-carousel.index', compact('items'));
    }
    
    public function create()
    {
        $nextOrder = InfoCarousel::max('order_position') + 1;
        return view('dashboard.info-carousel.create', compact('nextOrder'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'link' => 'nullable|url|max:255',
            'link_text' => 'nullable|string|max:100',
            'order_position' => 'integer',
            'active' => 'boolean'
        ]);
        
        InfoCarousel::create([
            'message' => $request->message,
            'link' => $request->link,
            'link_text' => $request->link_text,
            'order_position' => $request->order_position ?? InfoCarousel::count() + 1,
            'active' => $request->has('active')
        ]);
        
        return redirect()->route('admin.info-carousel.index')
            ->with('success', 'Mensaje creado exitosamente.');
    }
    
    public function edit($id)
    {
        $item = InfoCarousel::findOrFail($id);
        return view('dashboard.info-carousel.edit', compact('item'));
    }
    
    public function update(Request $request, $id)
    {
        $item = InfoCarousel::findOrFail($id);
        
        $request->validate([
            'message' => 'required|string|max:500',
            'link' => 'nullable|url|max:255',
            'link_text' => 'nullable|string|max:100',
            'order_position' => 'integer',
            'active' => 'boolean'
        ]);
        
        $item->update([
            'message' => $request->message,
            'link' => $request->link,
            'link_text' => $request->link_text,
            'order_position' => $request->order_position,
            'active' => $request->has('active')
        ]);
        
        return redirect()->route('admin.info-carousel.index')
            ->with('success', 'Mensaje actualizado exitosamente.');
    }
    
    public function destroy($id)
    {
        $item = InfoCarousel::findOrFail($id);
        $item->delete();
        
        return redirect()->route('admin.info-carousel.index')
            ->with('success', 'Mensaje eliminado exitosamente.');
    }
    
    public function updateOrder(Request $request)
    {
        $orders = $request->input('orders');
        
        foreach ($orders as $orderData) {
            InfoCarousel::where('id', $orderData['id'])
                ->update(['order_position' => $orderData['position']]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function toggle($id)
    {
        $item = InfoCarousel::findOrFail($id);
        $item->active = !$item->active;
        $item->save();
        
        return response()->json(['active' => $item->active]);
    }
}