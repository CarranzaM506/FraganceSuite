<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodePromotion extends Model
{
    protected $table = 'code_promotion';
    protected $primaryKey = 'idcode_promotion';
    public $timestamps = false;

    protected $fillable = [
        'value',
        'start_date',
        'end_date',
        'code_promotion',
    ];

}
