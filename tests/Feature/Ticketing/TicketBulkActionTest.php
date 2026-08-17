<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Lareon\Modules\Ticketing\App\Action\TicketBulkAction;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected Role $managerRole;

    protected Role $chiefRole;

    protected User $manager;

    protected User $chief;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRole = Role::factory()->create([
            'title' => 'ticket manager',
        ]);

        $this->chiefRole = Role::factory()->create([
            'title' => 'chief ticket manager',
        ]);

        $this->manager = User::factory()->create();
        $this->chief = User::factory()->create();

        $this->manager->assignRole($this->managerRole);
        $this->chief->assignRole($this->chiefRole);
    }

    public function test_invalid_action_throws_exception(): void
    {
        $this->actingAs($this->manager);

        $this->expectException(InvalidArgumentException::class);

        app(TicketBulkAction::class)
            ->handle('invalid');
    }

    public function test_manager_can_review_pending_tickets(): void
    {
        Ticket::factory()->count(3)->create();

        $this->actingAs($this->manager);

        $count = app(TicketBulkAction::class)
            ->handle('review');

        $this->assertSame(3, $count);

        $this->assertDatabaseCount(
            'tickets_approvals',
            3
        );
    }

    public function test_manager_can_bulk_approve(): void
    {
        $tickets = Ticket::factory()->count(3)->create();

        foreach ($tickets as $ticket) {
            TicketApproval::factory()->create([
                'ticket_id' => $ticket->id,
                'admin_id' => $this->manager->id,
                'role_id' => $this->managerRole->id,
                'status' => TicketStatusEnum::IN_REVIEW->value,
            ]);
        }

        $this->actingAs($this->manager);

        $count = app(TicketBulkAction::class)
            ->handle('approve');

        $this->assertSame(3, $count);

        $this->assertSame(
            3,
            TicketApproval::query()
                          ->where('status', TicketStatusEnum::APPROVED->value)
                          ->count()
        );
    }

    public function test_manager_can_bulk_reject(): void
    {
        $tickets = Ticket::factory()->count(3)->create();

        foreach ($tickets as $ticket) {
            TicketApproval::factory()->create([
                'ticket_id' => $ticket->id,
                'admin_id' => $this->manager->id,
                'role_id' => $this->managerRole->id,
                'status' => TicketStatusEnum::IN_REVIEW->value,
            ]);
        }

        $this->actingAs($this->manager);

        $count = app(TicketBulkAction::class)
            ->handle('reject');

        $this->assertSame(3, $count);

        $this->assertSame(
            3,
            TicketApproval::query()
                          ->where('status', TicketStatusEnum::REJECTED->value)
                          ->count()
        );
    }

    public function test_chief_can_review_only_manager_approved_tickets(): void
    {
        $approvedTicket = Ticket::factory()->create();
        $pendingTicket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $approvedTicket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::APPROVED->value,
        ]);

        $this->actingAs($this->chief);

        $count = app(TicketBulkAction::class)
            ->handle('review');

        $this->assertSame(1, $count);

        $this->assertDatabaseHas('tickets_approvals', [
            'ticket_id' => $approvedTicket->id,
            'admin_id' => $this->chief->id,
            'role_id' => $this->chiefRole->id,
        ]);

        $this->assertDatabaseMissing('tickets_approvals', [
            'ticket_id' => $pendingTicket->id,
            'admin_id' => $this->chief->id,
            'role_id' => $this->chiefRole->id,
        ]);
    }
}
