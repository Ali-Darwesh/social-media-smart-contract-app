<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'contract_address',
        'client',
        'serviceProvider',
        'totalAmount',
        'status',
        'pdf_path',
    ];

    // Contract.php

    public function users()
    {
        return $this->belongsToMany(User::class, 'contract_user')
            ->withPivot('role', 'user_address')
            ->withTimestamps();
    }

    // Optional: Contract has many clauses
    public function clauses()
    {
        return $this->hasMany(Clause::class)->orderBy('created_at', 'asc');
    }
    public function approvedClauses()
    {
        return $this->hasMany(Clause::class, 'contract_id')
            ->where('approved_by_a', true)
            ->where('approved_by_b', true)
            ->orderBy('created_at', 'asc');
    }
}
