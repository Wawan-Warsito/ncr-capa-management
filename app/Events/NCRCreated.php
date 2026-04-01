<?php

namespace App\Events;

use App\Models\NCR;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NCRCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ncr;

    /**
     * Create a new event instance.
     */
    public function __construct(NCR $ncr)
    {
        $this->ncr = $ncr;
    }
}
