<?php

namespace Lareon\Modules\Ticketing\App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Lareon\Modules\Ticketing\App\Notifications\NewTicketNotificationToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToAdmin;
use Lareon\Modules\Ticketing\App\Notifications\UpdateTicketStatusToClient;
use Lareon\Modules\User\App\Http\Resources\UserResource;
use Lareon\Modules\User\App\Models\User;

class SendByApiListener implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 30;
    public array $backoff = [3600];
    public int $timeout = 30;

    public function __construct(public Ticket $ticket) {}

    public function handle(): void
    {
        $ticket = $this->ticket;
        $idempotencyKey = "ticket:{$ticket->id}:approved";
        $apiRequest = TicketApi::query()->firstOrCreate(
            [
                'ticket_id'       => $ticket->id,
                'idempotency_key' => $idempotencyKey,
            ],
            [
                'attempt' => 0,
                'status'  => 'pending',
            ]);

        if ($apiRequest->status === 'success') return;

        $apiRequest->increment('attempt');

        $apiRequest->update(['status' => 'processing', 'sent_at' => now(), 'error_message' => null,]);

        try {
            $response = Http::acceptJson()->timeout(15)
                            ->connectTimeout(5)
                            ->withHeaders(['Idempotency-Key' => $idempotencyKey,])
                            ->post(config('services.ticketing.api.url'),
                                [
                                    'ticket' => ['id' => $ticket->id, 'title' => $ticket->title, 'body' => $ticket->body, 'file' => $ticket->file,],
                                    'user'   => UserResource::make($ticket->creator),
                                ]);

            $apiRequest->update([
                'status'        => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->body(),
                'completed_at'  => $response->successful() ? now() : null,
                'error_message' => $response->successful() ? null : $response->body(),
            ]);
            Log::info($apiRequest->refresh());

            if ($response->failed()) $response->throw();

        } catch (\Throwable $exception) {
            $apiRequest->update([
                'status'        => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
            Log::error($apiRequest->refresh());
            throw $exception;
        }
    }
}
