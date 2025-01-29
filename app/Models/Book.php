<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'pages',
        'word_count',
        'category',
        'genre',
        'start_date',
        'end_date',
        'rating',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pages' => 'integer',
        'word_count' => 'integer',
        'category' => 'integer',
        'genre' => 'integer',
        'rating' => 'integer',
    ];
}
