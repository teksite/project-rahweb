<?php

namespace Tests\Feature\Ticketing;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketApprovalObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_change_dispatches_event(): void
    {
        Event::fake([
            UpdateTicketStatusEvent::class,
        ]);

        $user = User::factory()->create();

        $role = Role::factory()->create([
            'title' => 'ticket manager',
        ]);

        $ticket = Ticket::factory()->create();

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $user->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $approval->update([
            'status' => TicketStatusEnum::APPROVED->value,
        ]);

        Event::assertDispatched(
            UpdateTicketStatusEvent::class,
            function ($event) use ($ticket, $approval) {
                return $event->ticket->id === $ticket->id
                    && $event->approval->id === $approval->id;
            }
        );
    }

    public function test_review_change_does_not_dispatch_status_event(): void
    {
        Event::fake([
            UpdateTicketStatusEvent::class,
        ]);

        $user = User::factory()->create();

        $role = Role::factory()->create([
            'title' => 'ticket manager',
        ]);

        $ticket = Ticket::factory()->create();

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $user->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $approval->update([
            'review' => 'Some review',
        ]);

        Event::assertNotDispatched(
            UpdateTicketStatusEvent::class
        );
    }
}
