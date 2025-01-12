<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sarana extends Model
{
    protected $table = 'sarana';

    protected $fillable = [
        'sarana_id',
        'title',
        'image',
        'status'
    ];
}
