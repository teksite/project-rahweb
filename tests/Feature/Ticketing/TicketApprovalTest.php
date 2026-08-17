<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Logics\ApprovalTicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Role $managerRole;

    protected Role $chiefRole;

    protected User $manager;

    protected User $chief;

    protected User $user;

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
        $this->user = User::factory()->create();

        $this->manager->assignRole($this->managerRole);
        $this->chief->assignRole($this->chiefRole);
    }

    public function test_manager_can_create_approval(): void
    {
        $ticket = Ticket::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->manager);

        $result = app(ApprovalTicketLogic::class)
            ->prepareApproval($ticket);

        $this->assertTrue($result->success);

        $this->assertDatabaseHas('tickets_approvals', [
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);
    }

    public function test_manager_can_approve_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $this->actingAs($this->manager);

        app(ApprovalTicketLogic::class)->update(
            $ticket,
            [
                'status' => TicketStatusEnum::APPROVED->value,
                'review' => null,
            ]
        );

        $this->assertDatabaseHas('tickets_approvals', [
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::APPROVED->value,
        ]);
    }

    public function test_manager_can_reject_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $this->actingAs($this->manager);

        app(ApprovalTicketLogic::class)->update(
            $ticket,
            [
                'status' => TicketStatusEnum::REJECTED->value,
                'review' => 'The ticket was rejected.',
            ]
        );

        $this->assertDatabaseHas('tickets_approvals', [
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::REJECTED->value,
            'review' => 'The ticket was rejected.',
        ]);
    }

    public function test_ticket_is_rejected_when_any_approval_is_rejected(): void
    {
        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'status' => TicketStatusEnum::REJECTED->value,
        ]);

        $ticket->refresh();

        $this->assertSame(
            TicketStatusEnum::REJECTED,
            $ticket->status
        );
    }
}
