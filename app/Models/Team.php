<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;
    

    protected $fillable = [
        'name',
        'description',
        'avatar',
    ];

    // A team has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // A team has many tasks through users
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, User::class);
    }

    // Return full URL for avatar if present
    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar)) {
            return null;
        }

        return url('uploads/teams/' . $this->avatar);
    }

    protected $appends = ['avatar_url'];
}
