<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'images',
        'title',
        'subtitle',
        'description',
        'tags',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
