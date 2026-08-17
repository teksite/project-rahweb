<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Lareon\Modules\Ticketing\App\queries\TicketListQuery;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected Role $ticketManagerRole;

    protected Role $chiefTicketManagerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticketManagerRole = Role::query()->firstOrCreate([
            'title' => 'ticket manager',
        ]);

        $this->chiefTicketManagerRole = Role::query()->firstOrCreate([
            'title' => 'chief ticket manager',
        ]);
    }

    protected function userWithRole(string $roleTitle): User
    {
        $user = User::factory()->create();

        $role = match ($roleTitle) {
            'ticket manager' => $this->ticketManagerRole,
            'chief ticket manager' => $this->chiefTicketManagerRole,
            default => Role::query()->firstOrCreate([
                'title' => $roleTitle,
            ]),
        };

        $user->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        return $user;
    }

    public function test_manager_can_see_ticket_without_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        $this->actingAs($manager);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertTrue(
            $ids->contains($ticket->id)
        );
    }

    public function test_manager_can_see_own_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $this->ticketManagerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW,
        ]);

        $this->actingAs($manager);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertTrue(
            $ids->contains($ticket->id)
        );
    }

    public function test_manager_cannot_see_another_manager_approval(): void
    {
        $manager1 = $this->userWithRole(
            'ticket manager'
        );

        $manager2 = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager2->id,
            'role_id' => $this->ticketManagerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW,
        ]);

        $this->actingAs($manager1);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertFalse(
            $ids->contains($ticket->id)
        );
    }

    public function test_chief_cannot_see_ticket_before_first_manager_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $chief = $this->userWithRole(
            'chief ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $this->ticketManagerRole->id,
            'status' => TicketStatusEnum::IN_REVIEW,
        ]);

        $this->actingAs($chief);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertFalse(
            $ids->contains($ticket->id)
        );
    }

    public function test_chief_can_see_ticket_after_first_manager_approval(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $chief = $this->userWithRole(
            'chief ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $this->ticketManagerRole->id,
            'status' => TicketStatusEnum::APPROVED,
        ]);

        $this->actingAs($chief);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertTrue(
            $ids->contains($ticket->id)
        );
    }

    public function test_chief_cannot_see_rejected_ticket(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $chief = $this->userWithRole(
            'chief ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $manager->id,
            'role_id' => $this->ticketManagerRole->id,
            'status' => TicketStatusEnum::REJECTED,
        ]);

        $this->actingAs($chief);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertFalse(
            $ids->contains($ticket->id)
        );
    }

    public function test_manager_cannot_see_ticket_that_has_api_request(): void
    {
        $manager = $this->userWithRole(
            'ticket manager'
        );

        $ticket = Ticket::factory()->create();

        TicketApi::query()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => 'ticket:' . $ticket->id . ':approved',
            'attempt' => 1,
            'status' => 0,
        ]);

        $this->actingAs($manager);

        $result = app(TicketListQuery::class)->paginate();

        $ids = $result->getCollection()->pluck('id');

        $this->assertFalse(
            $ids->contains($ticket->id)
        );
    }
}
