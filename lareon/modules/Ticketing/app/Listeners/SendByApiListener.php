<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\NewTicketEvent;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Jobs\TicketToApiJob;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToClient;
use Lareon\Modules\User\App\Models\User;

class SendByApiListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UpdateTicketStatusEvent $event): void
    {
        $ticket = $event->ticket->refresh();

        if ($ticket->approvals()->where('status' , TicketStatusEnum::APPROVED)->count() >=2) {
            TicketToApiJob::dispatch($ticket);
        }

    }
}
