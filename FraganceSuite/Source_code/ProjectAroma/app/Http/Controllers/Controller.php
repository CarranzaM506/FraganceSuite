<?php

namespace App\Http\Controllers;

use App\Models\InfoCarousel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    public function __construct()
    {
        // Compartir datos del carrusel SOLO en vistas que NO son de autenticación
        view()->composer('*', function ($view) {
            // Obtener el nombre de la ruta actual
            $currentRouteName = Route::currentRouteName();
            
            // Lista de rutas donde NO queremos mostrar el carrusel (basado en tu route:list)
            $excludedRoutes = [
                // Autenticación
                'login',
                'register',
                'password.request',
                'password.email',
                'password.reset',
                'password.confirm',
                'password.update',
                'logout',
                // Verificación de email
                'verification.notice',
                'verification.send',
                'verification.verify',
                // Confirmar contraseña
                'confirm-password',
                // Two-factor (si usas)
                'two-factor.login',
            ];
            
            // También excluir por path para rutas que no tienen nombre
            $excludedPaths = [
                'login',
                'register',
                'forgot-password',
                'reset-password',
                'verify-email',
                'confirm-password',
                'logout',
            ];
            
            // Verificar si la ruta actual está excluida por nombre
            $shouldExclude = false;
            
            if ($currentRouteName) {
                foreach ($excludedRoutes as $excluded) {
                    if ($currentRouteName === $excluded || str_starts_with($currentRouteName, $excluded)) {
                        $shouldExclude = true;
                        break;
                    }
                }
            }
            
            // También excluir por path si es necesario
            if (!$shouldExclude) {
                $currentPath = request()->path();
                foreach ($excludedPaths as $path) {
                    if (str_starts_with($currentPath, $path)) {
                        $shouldExclude = true;
                        break;
                    }
                }
            }
            
            // Solo cargar el carrusel si la vista NO está excluida
            if (!$shouldExclude) {
                try {
                    $infoCarouselItems = InfoCarousel::getActiveItems();
                    $view->with('infoCarouselItems', $infoCarouselItems);
                } catch (\Exception $e) {
                    $view->with('infoCarouselItems', collect());
                }
            } else {
                // Pasar un carrusel vacío a las vistas excluidas
                $view->with('infoCarouselItems', collect());
            }
        });
    }
}