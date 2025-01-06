<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'image',
        'title',
        'subtitle',
        'description',
        'tags',
    ];
}
