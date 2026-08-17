<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lareon\Modules\Ticketing\App\Enums\ApiStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_request_belongs_to_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $request = TicketApi::query()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => 'ticket:'.$ticket->id.':approved',
            'attempt' => 1,
            'status' => ApiStatusEnum::PENDING,
        ]);

        $this->assertTrue(
            $request->ticket->is($ticket)
        );
    }

    public function test_api_status_is_cast_to_enum(): void
    {
        $ticket = Ticket::factory()->create();

        $request = TicketApi::query()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => 'ticket:'.$ticket->id.':cast',
            'attempt' => 1,
            'status' => ApiStatusEnum::PROCESSING,
        ]);

        $this->assertSame(
            ApiStatusEnum::PROCESSING,
            $request->fresh()->status
        );
    }

    public function test_api_request_can_store_response_body(): void
    {
        $ticket = Ticket::factory()->create();

        $request = TicketApi::query()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => 'ticket:'.$ticket->id.':response',
            'attempt' => 1,
            'status' => ApiStatusEnum::SUCCESS,
            'response_body' => [
                'message' => 'success',
            ],
            'response_code' => 200,
        ]);

        $request->refresh();

        $this->assertSame(
            [
                'message' => 'success',
            ],
            $request->response_body
        );
    }

    public function test_deleting_ticket_deletes_api_requests(): void
    {
        $ticket = Ticket::factory()->create();

        TicketApi::query()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => 'ticket:'.$ticket->id.':cascade',
            'attempt' => 1,
            'status' => ApiStatusEnum::PENDING,
        ]);

        $ticket->delete();

        $this->assertDatabaseMissing(
            'tickets_api_requests',
            [
                'ticket_id' => $ticket->id,
            ]
        );
    }
}
