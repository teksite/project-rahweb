<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Lareon\Modules\Ticketing\App\queries\TicketListQuery;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;
use Teksite\Authorize\Models\Role;

class TicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected Role $managerRole;

    protected Role $chiefRole;

    protected User $manager;

    protected User $anotherManager;

    protected User $chief;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRole = Role::query()->create([
            'title' => 'ticket manager',
        ]);

        $this->chiefRole = Role::query()->create([
            'title' => 'chief ticket manager',
        ]);

        $this->manager = User::factory()->create();

        $this->anotherManager = User::factory()->create();

        $this->chief = User::factory()->create();

        $this->manager->assignRole($this->managerRole);

        $this->anotherManager->assignRole($this->managerRole);

        $this->chief->assignRole($this->chiefRole);
    }

    public function test_manager_can_see_ticket_without_approval(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        $this->actingAs($this->manager);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertTrue(
            $result->contains('id', $ticket->id)
        );
    }

    public function test_manager_can_see_own_approval(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'round' => 1,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $this->actingAs($this->manager);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertTrue(
            $result->contains('id', $ticket->id)
        );
    }

    public function test_manager_cannot_see_another_manager_ticket(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->anotherManager->id,
            'role_id' => $this->managerRole->id,
            'round' => 1,
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);

        $this->actingAs($this->manager);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertFalse(
            $result->contains('id', $ticket->id)
        );
    }

    public function test_chief_can_see_manager_approved_ticket(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'round' => 1,
            'status' => TicketStatusEnum::APPROVED->value,
        ]);

        $this->actingAs($this->chief);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertTrue(
            $result->contains('id', $ticket->id)
        );
    }

    public function test_chief_cannot_see_ticket_without_manager_approval(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        $this->actingAs($this->chief);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertFalse(
            $result->contains('id', $ticket->id)
        );
    }

    public function test_chief_cannot_see_ticket_after_his_approval(): void
    {
        $ticket = Ticket::factory()
                        ->createdBy($this->manager)
                        ->create();

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->manager->id,
            'role_id' => $this->managerRole->id,
            'round' => 1,
            'status' => TicketStatusEnum::APPROVED->value,
        ]);

        TicketApproval::factory()->create([
            'ticket_id' => $ticket->id,
            'admin_id' => $this->chief->id,
            'role_id' => $this->chiefRole->id,
            'round' => 1,
            'status' => TicketStatusEnum::APPROVED->value,
        ]);

        $this->actingAs($this->chief);

        $result = app(TicketListQuery::class)->paginate();

        $this->assertFalse(
            $result->contains('id', $ticket->id)
        );
    }
}
