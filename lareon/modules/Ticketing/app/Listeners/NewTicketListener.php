<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\InteractsWithQueue;
use Lareon\Modules\Ticketing\App\Events\NewTicketEvent;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotification;
use Lareon\Modules\User\App\Models\User;

class NewTicketListener
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
    public function handle(NewTicketEvent $event): void
    {
        $ticket = $event->ticket;

        $users=User::query()->whereHas('roles', function (Builder $query) use ($ticket) {
            $query->where('title' ,'ticket manager');
        });
        foreach ($users as $user) {
            $user->notify(new NewTicketNotification($ticket));
        }

    }
}
