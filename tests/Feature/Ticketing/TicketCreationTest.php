<?php

namespace Tests\Feature\Ticketing;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $result = app(TicketLogic::class)->create([
            'user_id' => $user->id,
            'title' => 'Test ticket',
            'body' => 'This is a test ticket.',
            'file' => null,
        ]);

        $this->assertTrue($result->success);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'title' => 'Test ticket',
            'body' => 'This is a test ticket.',
        ]);
    }

    public function test_ticket_belongs_to_creator(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals(
            $user->id,
            $ticket->creator->id
        );
    }

    public function test_ticket_can_have_attachment(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'file' => 'tickets/1/test.pdf',
        ]);

        $this->assertSame(
            'tickets/1/test.pdf',
            $ticket->file
        );
    }
}
