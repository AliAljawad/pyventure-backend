<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProgress extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'user_progress';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'level_id', 'is_completed', 'score', 'attempts', 'last_updated'
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
