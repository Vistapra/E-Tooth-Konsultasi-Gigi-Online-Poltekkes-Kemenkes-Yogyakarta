<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class ChMessage extends Model
{
    use UUID;

    protected $fillable = [
        'from_id',
        'to_id',
        'body',
        'attachment',
        'seen',
        'is_ai_response',
        'ai_confidence',
        'sentiment',
        'keywords'
    ];

    protected $casts = [
        'seen' => 'boolean',
        'is_ai_response' => 'boolean',
        'ai_confidence' => 'float',
        'keywords' => 'array'
    ];

    public function from()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function scopeUnreadForDoctor($query, $doctorId)
    {
        return $query->where('to_id', $doctorId)
            ->where('seen', false)
            ->where('is_ai_response', false);
    }

    public function scopeAiResponses($query)
    {
        return $query->where('is_ai_response', true);
    }

    public function getFromIdAttribute($value)
    {
        return strval($value);
    }

    public function setFromIdAttribute($value)
    {
        $this->attributes['from_id'] = strval($value);
    }
}