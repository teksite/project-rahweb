<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToClient;
use Lareon\Modules\User\App\Models\User;

class SendByApiListener
{
    /**
     * Create the event listener.
     */
    public function __construct(public Ticket $ticket)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UpdateTicketStatusEvent $event): void
    {
        $ticket = $event->ticket;

        if ($ticket->approvals()->where('status' , TicketStatusEnum::APPROVED)->count() >= 2 ) {

        }



    }
}
