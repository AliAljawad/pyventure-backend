<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress';


    protected $fillable = [
        'user_id',
        'level_id',
        'is_completed',
        'score',
        'attempts',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'score' => 'integer',
        'attempts' => 'integer',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}