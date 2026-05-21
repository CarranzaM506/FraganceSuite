<?php

namespace App\Http\Controllers;

use App\Models\InfoCarousel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    public function __construct()
    {
        // Compartir datos del carrusel con TODAS las vistas
        view()->composer('*', function ($view) {
            try {
                $infoCarouselItems = InfoCarousel::getActiveItems();
                $view->with('infoCarouselItems', $infoCarouselItems);
            } catch (\Exception $e) {
                // Si la tabla no existe, no hacer nada
                $view->with('infoCarouselItems', collect());
            }
        });
    }
}