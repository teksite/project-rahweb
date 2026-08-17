<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToClient;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function role(string $title): Role
    {
        return Role::query()->firstOrCreate([
            'title' => $title,
        ]);
    }

    public function test_approved_notification_contains_approved_status(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = User::factory()->create();

        $role = $this->role(
            'ticket manager'
        );

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $notification = new UpdateTicketStatusToClient(
            $ticket,
            $approval
        );

        $mail = $notification->toMail(
            new AnonymousNotifiable()
        );

        $this->assertStringContainsString(
            'approved',
            strtolower($mail->render())
        );
    }

    public function test_rejected_notification_contains_rejection_reason(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = User::factory()->create();

        $role = $this->role(
            'ticket manager'
        );

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::REJECTED,
            'review' => 'Invalid information',
        ]);

        $notification = new UpdateTicketStatusToClient(
            $ticket,
            $approval
        );

        $mail = $notification->toMail(
            new AnonymousNotifiable()
        );

        $this->assertStringContainsString(
            'Invalid information',
            $mail->render()
        );
    }

    public function test_notification_to_array_contains_expected_data(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = User::factory()->create();

        $role = $this->role(
            'ticket manager'
        );

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $notification = new UpdateTicketStatusToClient(
            $ticket,
            $approval
        );

        $data = $notification->toArray($ticket->creator);

        $this->assertSame(
            $ticket->id,
            $data['ticket_id']
        );

        $this->assertSame(
            $approval->id,
            $data['approval_id']
        );

        $this->assertSame(
            TicketStatusEnum::APPROVED->value,
            $data['status']
        );
    }
}
