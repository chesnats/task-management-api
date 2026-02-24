<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'team_id',
        'avatar',
    ];

    // Automatically hash password
    protected $casts = [
        'password' => 'hashed',
    ];

    // A user belongs to a team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // A user can have many posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // A user can have many tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is team leader
    public function isTeamLeader()
    {
        return $this->role === 'team_leader';
    }

    // Check if user is regular user
    public function isRegularUser()
    {
        return $this->role === 'user';
    }

    // Return full URL for avatar if present
    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar)) {
            return null;
        }

        return url('uploads/users/' . $this->avatar);
    }

    protected $appends = ['avatar_url'];
}
