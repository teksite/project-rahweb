<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Lareon\Modules\Ticketing\App\Events\NewTicketEvent;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToClient;
use Lareon\Modules\User\App\Models\User;

class NewTicketListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NewTicketEvent $event): void
    {
        $ticket = $event->ticket;

        User::query()
            ->whereHas('roles', function ($query) {
                $query->where('title', 'ticket manager');
            })->chunk(50, function ($users) use ($ticket) {
                foreach ($users as $user) {
                    $user->notify(new NewTicketNotificationToAdmin($ticket));
                }
            });

        $ticket->creator?->notify(
            new NewTicketNotificationToClient($ticket)
        );
    }
}
