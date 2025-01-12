<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'berita_id',
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
