<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
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
