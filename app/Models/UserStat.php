<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    use HasFactory;

    protected $table = 'user_stats';


    protected $fillable = [
        'user_id',
        'total_attempts',
        'total_completed_levels',
        'total_score',
        'time_spent',
    ];

    protected $casts = [
        'total_attempts' => 'integer',
        'total_completed_levels' => 'integer',
        'total_score' => 'integer',
        'time_spent' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
