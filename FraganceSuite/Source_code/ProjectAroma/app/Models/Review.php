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
        'is_blocked',
        'moderation_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduct', 'idproduct');
    }
}
