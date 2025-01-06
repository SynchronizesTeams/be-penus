<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sarana extends Model
{
    protected $table = 'sarana';

    protected $fillable = [
        'title',
        'image'
    ];
}
