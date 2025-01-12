<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'berita_id',
        'author',
        'images',
        'title',
        'subtitle',
        'description',
        'tags',
        'status',
    ];

    public function Author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    protected $casts = [
        'tags' => 'array',
    ];
}
