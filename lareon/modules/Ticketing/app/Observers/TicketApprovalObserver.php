<?php

namespace Lareon\Modules\Ticketing\App\Observers;

use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;

class TicketApprovalObserver
{
    public function updated(TicketApproval $approval): void
    {
        $ticket= $approval->ticket;
        dd($ticket);
        event(new UpdateTicketStatusEvent($ticket , $approval));
    }
}
