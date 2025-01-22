<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'post_id',
        'author_id',
    ];

 
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

   
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
