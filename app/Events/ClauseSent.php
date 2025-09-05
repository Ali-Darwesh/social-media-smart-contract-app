<?php

namespace App\Events;

use App\Models\Clause;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClauseSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clause;

    public function __construct(Clause $clause)
    {
        $this->clause = $clause;
    }

    // Broadcast to a private channel for the contract
    public function broadcastOn()
    {
        return new PrivateChannel('contract.' . $this->clause->contract_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->clause->id,
            'text' => $this->clause->text,
            'proposer_address' => $this->clause->proposer_address,
            'amount_usd' => $this->clause->amount_usd,
            'created_at' => $this->clause->created_at->toDateTimeString(),
        ];
    }
}
