<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'details',
        'author_id',
    ];

  
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

 
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

   
    public function videos()
    {
        return $this->hasMany(Video::class);
    }

  
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function reactions()
{
    return $this->hasMany(PostReaction::class);
}

public function likes()
{
    return $this->reactions()->where('type', 'like');
}

public function dislikes()
{
    return $this->reactions()->where('type', 'dislike');
}

}
