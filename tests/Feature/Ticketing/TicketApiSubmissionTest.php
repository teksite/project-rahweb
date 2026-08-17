<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lareon\Modules\Ticketing\App\Jobs\TicketToApiJob;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Lareon\Modules\User\App\Models\User;
use Tests\TestCase;

class TicketApiSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_is_successfully_sent_to_api(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
            ], 200),
        ]);

        Log::fake();

        $user = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
        ]);

        (new TicketToApiJob($ticket))->handle();

        $this->assertDatabaseHas('tickets_api_requests', [
            'ticket_id' => $ticket->id,
            'status' => 'success',
            'response_code' => 200,
            'attempt' => 1,
        ]);

        Http::assertSent(function ($request) use ($ticket) {
            return $request->url() === url('/api/endpoint')
                && $request->hasHeader(
                    'Idempotency-Key',
                    "ticket:{$ticket->id}:approved"
                );
        });
    }

    public function test_ticket_api_request_contains_ticket_data(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $user = User::factory()->create([
            'name' => 'Sina',
            'email' => 'sina@example.com',
        ]);

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'title' => 'My Ticket',
            'body' => 'Ticket body',
            'file' => 'tickets/test.pdf',
        ]);

        (new TicketToApiJob($ticket))->handle();

        Http::assertSent(function ($request) use ($ticket, $user) {
            $data = $request->data();

            return $data['ticket']['id'] === $ticket->id
                && $data['ticket']['title'] === 'My Ticket'
                && $data['ticket']['body'] === 'Ticket body'
                && $data['ticket']['file'] === 'tickets/test.pdf'
                && $data['user']['id'] === $user->id
                && $data['user']['email'] === 'sina@example.com';
        });
    }

    public function test_server_error_is_stored_as_failed(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => 'Internal Server Error',
            ], 500),
        ]);


        $ticket = Ticket::factory()->create();

        try {
            (new TicketToApiJob($ticket))->handle();
        } catch (\Throwable) {
            // Expected.
        }

        $this->assertDatabaseHas('tickets_api_requests', [
            'ticket_id' => $ticket->id,
            'status' => 'failed',
            'response_code' => 500,
            'attempt' => 1,
        ]);
    }

    public function test_successful_ticket_is_not_sent_twice(): void
    {
        Http::fake();

        $ticket = Ticket::factory()->create();

        TicketApi::factory()->create([
            'ticket_id' => $ticket->id,
            'idempotency_key' => "ticket:{$ticket->id}:approved",
            'attempt' => 1,
            'status' => 'success',
        ]);

        (new TicketToApiJob($ticket))->handle();

        Http::assertNothingSent();
    }
}
