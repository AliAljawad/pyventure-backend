<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Level extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['title', 'description', 'difficulty', 'category', 'question', 'solution'];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }
}
