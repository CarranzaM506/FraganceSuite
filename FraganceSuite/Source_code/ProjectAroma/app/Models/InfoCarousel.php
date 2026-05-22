<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoCarousel extends Model
{
    protected $table = 'info_carousel';
    
    protected $fillable = [
        'message',
        'link',
        'link_text',
        'order_position',
        'active'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'order_position' => 'integer'
    ];
    
    // Obtener solo los activos ordenados
    public static function getActiveItems()
    {
        return self::where('active', 1)
            ->orderBy('order_position', 'asc')
            ->get();
    }
}