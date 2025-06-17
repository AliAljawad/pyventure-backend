<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'rank',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_fa_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_fa_expires_at' => 'datetime',
    ];

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // MISSING RELATIONSHIPS
    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function stats()
    {
        return $this->hasOne(UserStat::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withTimestamps()
            ->withPivot('earned_at');
    }
}