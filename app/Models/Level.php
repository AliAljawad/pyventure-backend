<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
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
