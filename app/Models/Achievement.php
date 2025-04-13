<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Achievement extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['title', 'description', 'icon_url'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withTimestamps()
                    ->withPivot('earned_at');
    }
}
