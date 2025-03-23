<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image_id',
    ];

  
    public function profileImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

 
    public function comments()
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

 
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

  
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }


    public function friends()
    {
        return $this->belongsToMany(
            User::class,
            'friendships',
            'user_id',
            'friend_id'
        )->withPivot('status')->withTimestamps();
    }

   
    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_user');
    }
}
