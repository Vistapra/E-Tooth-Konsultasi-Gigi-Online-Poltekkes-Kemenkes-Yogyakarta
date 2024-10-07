<?php

namespace App\Models;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $table = 'doctor';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
