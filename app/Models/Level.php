<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'difficulty',
        'category',
        'question',
        'solution',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
