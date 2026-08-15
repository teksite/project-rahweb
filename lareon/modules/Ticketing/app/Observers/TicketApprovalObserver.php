<?php

namespace Lareon\Modules\Ticketing\App\Observers;

use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;

class TicketApprovalObserver
{
    public function updated(TicketApproval $approval): void
    {
        if (! $approval->wasChanged('status')) return;

        event(new UpdateTicketStatusEvent($approval->ticket, $approval));
    }
}
