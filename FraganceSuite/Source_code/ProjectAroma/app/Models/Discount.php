<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'discount';
    protected $primaryKey = 'iddiscount';
    public $timestamps = false;

    protected $fillable = [
        'value',
        'startdate',
        'enddate',
        'condition',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'iddiscount', 'iddiscount');
    }
}
