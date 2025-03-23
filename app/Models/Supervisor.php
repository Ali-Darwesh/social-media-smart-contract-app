<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Supervisor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable  ,HasRoles;
    protected $guard_name = 'supervisor-api';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
