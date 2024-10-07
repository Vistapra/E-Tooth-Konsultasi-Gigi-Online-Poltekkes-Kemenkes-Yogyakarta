<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasMessage;
use App\Traits\UUID;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, UUID, HasMessage;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $dates = [
        'last_activity_at',
    ];

    public function isOwner()
    {
        return $this->role === 'owner' && $this->hasRole('owner');
    }

    public function isDoctor()
    {
        return $this->role === 'doctor' && $this->hasRole('doctor');
    }

    public function isBuyer()
    {
        return $this->role === 'buyer' && $this->hasRole('buyer');
    }

    public function messages()
    {
        return $this->hasMany(ChMessage::class, 'from_id')->orWhere('to_id', $this->id);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChMessage::class, 'from_id')->latest();
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }
}