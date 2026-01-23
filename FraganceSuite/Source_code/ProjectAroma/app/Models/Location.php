<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'location';
    protected $primaryKey = 'idlocation';
    public $timestamps = false;

    protected $fillable = [
        'province',
        'canton',
        'district',
        'detail',
        'zipcode',
        'iduser',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'id');
    }
}
