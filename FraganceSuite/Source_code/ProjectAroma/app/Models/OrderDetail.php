<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'orderdetail';
    public $timestamps = false;

    protected $fillable = [
        'idorder',
        'idproduct',
        'quantity',
        'price'   
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'idorder', 'idorder');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'idproduct', 'idproduct');
    }
}
