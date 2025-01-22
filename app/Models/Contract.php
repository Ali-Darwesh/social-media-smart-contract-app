<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'details',
        'status',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'contract_user');
    }
}
