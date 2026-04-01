<?php

namespace App\Events;

use App\Models\NCR;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NCRStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ncr;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(NCR $ncr, string $oldStatus, string $newStatus)
    {
        $this->ncr = $ncr;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
