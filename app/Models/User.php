<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Automatically hash password
    protected $casts = [
        'password' => 'hashed',
    ];

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
}
