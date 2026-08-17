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

    protected function role(string $title): Role
    {
        return Role::query()->firstOrCreate([
            'title' => $title,
        ]);
    }

    protected function userWithRole(string $title): User
    {
        $user = User::factory()->create();

        $role = $this->role($title);

        $user->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        return $user;
    }

    public function test_manager_can_prepare_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        $this->actingAs($manager);

        $result = app(ApprovalTicketLogic::class)
            ->prepareApproval($ticket);

        $this->assertTrue(
            $result->success
        );

        $this->assertDatabaseHas(
            'tickets_approvals',
            [
                'ticket_id' => $ticket->id,
                'admin_id' => $manager->id,
                'status' => TicketStatusEnum::IN_REVIEW->value,
            ]
        );
    }

    public function test_non_manager_cannot_prepare_approval(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::factory()->create();

        $this->actingAs($user);

        $result = app(ApprovalTicketLogic::class)
            ->prepareApproval($ticket);

        $this->assertFalse(
            $result->success
        );

        $this->assertDatabaseCount(
            'tickets_approvals',
            0
        );
    }

    public function test_manager_can_update_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        $this->actingAs($manager);

        $result = app(ApprovalTicketLogic::class)
            ->update(
                $ticket,
                [
                    'status' => TicketStatusEnum::APPROVED,
                    'review' => 'Approved by manager',
                ]
            );

        $this->assertTrue(
            $result->success
        );

        $this->assertDatabaseHas(
            'tickets_approvals',
            [
                'ticket_id' => $ticket->id,
                'admin_id' => $manager->id,
                'status' => TicketStatusEnum::APPROVED->value,
                'review' => 'Approved by manager',
            ]
        );
    }
}
