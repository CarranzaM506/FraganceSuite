<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $primaryKey = 'idcart';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = ['iduser', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'id');
    }

    public function cartDetails()
    {
        return $this->hasMany(CartDetail::class, 'idcart', 'idcart');
    }
}