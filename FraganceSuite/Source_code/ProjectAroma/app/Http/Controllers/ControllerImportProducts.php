<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ControllerImportProducts extends Controller
{
    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ],[
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls).'
        ]);

        //logica para procesar el archivo y agregar los productos
        // Prueba rápida: importar usando un Import (recomendado)
    Excel::import(new ProductsImport, $request->file('file'));

    return back()->with('success', 'Importación completada.');
    }
}
