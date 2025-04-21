<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\RoleAssignmentTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Supervisor extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable  ,HasRoles,RoleAssignmentTrait;
    protected $guard_name = 'supervisor-api';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
