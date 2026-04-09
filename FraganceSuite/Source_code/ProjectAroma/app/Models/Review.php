<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'idreview';

    protected $fillable = [
        'idproduct',
        'iduser',
        'rating',
        'comment',
    ];
}
