<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Location;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'idorder';
    public $timestamps = false;

    protected $fillable = [
        'date',
        'state',
        'total',
        'purchasemethod',
        'guidenumber',
        'iduser',
        'idlocation',
        'sale_type',
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'idorder', 'idorder');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'idlocation', 'idlocation');
    }
}
