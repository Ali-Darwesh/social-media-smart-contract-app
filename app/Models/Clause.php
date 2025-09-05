<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clause extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'text',
        'proposer_address',
        'approved_by_a',
        'approved_by_b',
        'executed',
        'amount_usd',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
