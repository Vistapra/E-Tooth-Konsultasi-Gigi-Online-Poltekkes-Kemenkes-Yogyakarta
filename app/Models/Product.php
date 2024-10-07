<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getEmbedVideoLinkAttribute()
    {
        if (Str::contains($this->video_link, 'youtube.com') || Str::contains($this->video_link, 'youtu.be')) {
            $videoId = null;
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $this->video_link, $match)) {
                $videoId = $match[1];
            }
            if ($videoId) {
                return "https://www.youtube.com/embed/{$videoId}";
            }
        }
        return $this->video_link;
    }
}