<?php

namespace Tests\Unit\Ticketing\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function makeRole(string $title): Role
    {
        return Role::query()->firstOrCreate([
            'title' => $title,
        ]);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->create();

        $role = $this->makeRole($role);

        $user->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        return $user;
    }

    public function test_ticket_without_approval_is_pending(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertSame(
            TicketStatusEnum::PENDING,
            $ticket->status
        );
    }

    public function test_ticket_with_in_review_approval_is_in_review(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = $this->makeUserWithRole(
            'ticket manager'
        );

        $role = $manager->roles()->first();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::IN_REVIEW,
        ]);

        $this->assertSame(
            TicketStatusEnum::IN_REVIEW,
            $ticket->fresh()->status
        );
    }

    public function test_rejected_approval_makes_ticket_rejected(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = $this->makeUserWithRole(
            'ticket manager'
        );

        $role = $manager->roles()->first();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $role->id,
            'status' => TicketStatusEnum::REJECTED,
        ]);

        $this->assertSame(
            TicketStatusEnum::REJECTED,
            $ticket->fresh()->status
        );
    }

    public function test_two_approved_approvals_make_ticket_approved(): void
    {
        $ticket = Ticket::factory()->create();

        $manager1 = $this->makeUserWithRole(
            'ticket manager'
        );

        $manager2 = $this->makeUserWithRole(
            'chief ticket manager'
        );

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager1->id,
            'role_id' => $manager1->roles()->first()->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager2->id,
            'role_id' => $manager2->roles()->first()->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $this->assertSame(
            TicketStatusEnum::APPROVED,
            $ticket->fresh()->status
        );
    }

    public function test_rejection_has_priority_over_approvals(): void
    {
        $ticket = Ticket::factory()->create();

        $manager1 = $this->makeUserWithRole(
            'ticket manager'
        );

        $manager2 = $this->makeUserWithRole(
            'chief ticket manager'
        );

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager1->id,
            'role_id' => $manager1->roles()->first()->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager2->id,
            'role_id' => $manager2->roles()->first()->id,
            'status' => TicketStatusEnum::REJECTED,
        ]);

        $this->assertSame(
            TicketStatusEnum::REJECTED,
            $ticket->fresh()->status
        );
    }

    public function test_first_manager_approval_can_be_retrieved(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = $this->makeUserWithRole(
            'ticket manager'
        );

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $manager->roles()->first()->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $result = $ticket->fresh()
                         ->approvementByFirstAdmin();

        $this->assertNotNull($result);
        $this->assertSame(
            $approval->id,
            $result->id
        );
    }

    public function test_second_manager_approval_can_be_retrieved(): void
    {
        $ticket = Ticket::factory()->create();

        $manager = $this->makeUserWithRole(
            'chief ticket manager'
        );

        $approval = TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $manager->roles()->first()->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $result = $ticket->fresh()
                         ->approvementBySecondAdmin();

        $this->assertNotNull($result);
        $this->assertSame(
            $approval->id,
            $result->id
        );
    }
}
