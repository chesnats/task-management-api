<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
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
}
