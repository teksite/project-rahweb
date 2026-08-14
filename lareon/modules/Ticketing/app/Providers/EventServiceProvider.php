<?php

namespace Lareon\Modules\Ticketing\App\Providers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Lareon\Modules\Ticketing\App\Events\NewTicketEvent;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\NewTicketRequest;
use Lareon\Modules\Ticketing\App\Listeners\NewTicketListener;

class EventServiceProvider  extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        NewTicketEvent::class=>[
            NewTicketListener::class
        ]
    ];


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
