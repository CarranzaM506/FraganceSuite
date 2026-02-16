<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $table = 'hero';
    protected $primaryKey = 'idhero';
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'image',
        'active'
    ];

    // Scope para obtener el hero activo (solo 1)
    public function scopeActive($query)
    {
        return $query->where('active', 1)->limit(1);
    }
}