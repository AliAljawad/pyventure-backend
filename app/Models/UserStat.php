<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class UserStat extends Model
{
    use HasFactory, Notifiable;
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
