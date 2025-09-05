<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\RoleAssignmentTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasRoles, HasFactory, Notifiable;

    protected $guard_name = 'api';
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    protected $fillable = [
        'name',
        'email',
        'age',
        'is_banned',
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
    public function postReactions()
    {
        return $this->hasMany(PostReaction::class);
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
    public function pendingSentRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id')->where('status', 'pending');
    }

    public function pendingReceivedRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id')->where('status', 'pending');
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
        return $this->belongsToMany(Contract::class, 'contract_user')
            ->withPivot(['role', 'user_address', 'status'])
            ->withTimestamps();
    }
}
