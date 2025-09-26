<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusicTrack extends Model
{
    use HasFactory;
    protected $table = 'music_tracks';
    protected $guarded = ['id']; // Mengizinkan semua field diisi (mass assignable)
    protected $casts = [
        'releaseDate' => 'datetime',
    ];
}