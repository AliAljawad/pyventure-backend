<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $table = 'user_stats';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'total_attempts', 'total_completed_levels', 'total_score', 'time_spent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
