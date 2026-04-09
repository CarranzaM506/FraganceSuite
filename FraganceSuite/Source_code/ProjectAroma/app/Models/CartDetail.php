<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    use HasFactory;

    protected $table = 'cartdetail';
    protected $primaryKey = ['idcart', 'idproduct'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['idcart', 'idproduct', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'idcart');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduct', 'idproduct');
    }
}
