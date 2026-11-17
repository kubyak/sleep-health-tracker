<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sleepRecords()
    {
        return $this->hasMany(Sleep::class);
    }

    public function activityRecords()
    {
        return $this->hasMany(Activity::class);
    }

    public function healthData()
    {
        return $this->hasOne(UserHealthData::class);
    }
}
