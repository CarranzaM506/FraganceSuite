<?php
// app/Models/Favorite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table = 'favorite';
    
    // IMPORTANTE: No hay una sola columna como primary key
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'iduser',
        'idproduct'
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduct', 'idproduct');
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'id');
    }

    public function delete()
    {
        return static::where('iduser', $this->iduser)
            ->where('idproduct', $this->idproduct)
            ->delete();
    }
}