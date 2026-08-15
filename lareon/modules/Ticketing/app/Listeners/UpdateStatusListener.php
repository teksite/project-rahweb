<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToClient;
use Lareon\Modules\User\App\Models\User;

class UpdateStatusListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UpdateTicketStatusEvent $event): void
    {
        $ticket = $event->ticket;
        $approval = $event->approval;

        if ($approval->status === TicketStatusEnum::APPROVED) {
            User::query()->whereHas('roles', function ($query) {
                $query->where('title', 'chief ticket manager');
            })->chunk(50, function ($users) use ($approval, $ticket) {
                foreach ($users as $user) {
                    $user->notify(new UpdateTicketStatusToAdmin($ticket, $approval));
                }
            });
        }

        $ticket->creator?->notify(new UpdateTicketStatusToClient($ticket, $approval));
    }
}
