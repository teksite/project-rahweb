<?php

namespace Lareon\Modules\Ticketing\App\Jobs;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lareon\Modules\Ticketing\App\Enums\ApiStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Lareon\Modules\User\App\Http\Resources\UserResource;

class TicketToApiJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 24;

    public int $timeout = 30;
    public array $backoff = [60];

    /**
     * Create a new job instance.
     */
    public function __construct(public int|Ticket $ticket)
    {
        $this->afterCommit();
    }

    /** * Execute the job. */
    public function handle(): void
    {
        $ticket = $this->getTicket();

        $apiRequest = $this->getApiRequest($ticket);

        if ($this->alreadySucceeded($apiRequest)) return;

        $this->markAsProcessing($apiRequest);

        try {
            $response = $this->sendRequest($ticket, $apiRequest);
            if ($response->successful()) {
                $this->markAsSuccessful($apiRequest, $response);
                return;
            }
            $this->markAsFailed($apiRequest, $response);
            if ($response->serverError()) {
                $response->throw();
            }
        } catch (\Throwable $exception) {
            $this->markAsException($apiRequest, $exception);
            throw $exception;
        }
    }

    /**
     * Get ticket with required relationships.
     */
    protected function getTicket(): Ticket
    {
        $ticket = $this->ticket;
        if ($ticket instanceof Ticket) return $ticket;
        return Ticket::query()->with('creator')->findOrFail($ticket);
    }

    /**
     * Get or create API request record.
     */
    protected function getApiRequest(Ticket $ticket): TicketApi
    {
        return TicketApi::query()->firstOrCreate(
            [
                'ticket_id'       => $ticket->id,
                'idempotency_key' => $this->idempotencyKey($ticket),],
            [
                'attempt' => 0,
                'status'  => ApiStatusEnum::PENDING->value
            ]);
    }

    /** * Generate idempotency key. */
    protected function idempotencyKey(Ticket $ticket): string
    {
        return "ticket:{$ticket->id}:approved";
    }

    /**
     * Determine whether request has already succeeded.
     */
    protected function alreadySucceeded(TicketApi $apiRequest): bool
    {
        return $apiRequest->status === ApiStatusEnum::SUCCESS;
    }

    /**
     * Mark API request as processing.
     */
    protected function markAsProcessing(TicketApi $apiRequest): void
    {
        $apiRequest->increment('attempt');
        $apiRequest->update([
            'status'        => ApiStatusEnum::PROCESSING->value,
            'sent_at'       => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Send ticket to external API.
     */
    protected function sendRequest(Ticket $ticket, TicketApi $apiRequest): PromiseInterface|LazyPromise|Response
    {
        return Http::acceptJson()
                   ->withoutVerifying()
                   ->timeout(15)
                   ->connectTimeout(5)
                   ->withHeaders(['Idempotency-Key' => $apiRequest->idempotency_key,])
                   ->post(url('/api/endpoint'), $this->payload($ticket));
    }

    /** * Build API payload. */
    protected function payload(Ticket $ticket): array
    {
        return [
            'ticket' => [
                'id'    => $ticket->id,
                'title' => $ticket->title,
                'body'  => $ticket->body,
                'file'  => $ticket->file,],
            'user'   => [
                'id'    => $ticket->creator?->id,
                'name'  => $ticket->creator?->name,
                'email' => $ticket->creator?->email,
            ],
        ];
    }

    /**
     * Mark API request as successful.
     */
    protected function markAsSuccessful(TicketApi $apiRequest, Response $response): void
    {
        $apiRequest->update([
            'status'        => ApiStatusEnum::SUCCESS->value,
            'response_code' => $response->status(),
            'response_body' => $response->body(),
            'completed_at'  => now(),
            'error_message' => null,
        ]);

        Log::driver('job')->info(
            'Ticket successfully sent to external API.',
            ['ticket_id' => $apiRequest->ticket_id, 'api_request_id' => $apiRequest->id, 'attempt' => $apiRequest->attempt, 'response_code' => $response->status(),]);
    }

    /** * Mark API request as failed because of HTTP response. */
    protected function markAsFailed(TicketApi $apiRequest, Response $response): void
    {
        $apiRequest->update(['status' => ApiStatusEnum::FAILED, 'response_code' => $response->status(), 'response_body' => $response->body(), 'error_message' => $response->body(),]);
        Log::driver('job')->warning('Ticket API request failed.', ['ticket_id' => $apiRequest->ticket_id, 'api_request_id' => $apiRequest->id, 'attempt' => $apiRequest->attempt, 'response_code' => $response->status(),]);
    }

    /** * Mark API request as failed because of exception. */
    protected function markAsException(TicketApi $apiRequest, \Throwable $exception): void
    {
        $apiRequest->update(['status' => ApiStatusEnum::FAILED, 'error_message' => $exception->getMessage(),]);
        Log::driver('job')->error('Ticket API request threw an exception.', ['ticket_id' => $apiRequest->ticket_id, 'api_request_id' => $apiRequest->id, 'attempt' => $apiRequest->attempt, 'exception' => $exception::class, 'message' => $exception->getMessage(),]);
        Log::error('Ticket API request threw an exception.', ['ticket_id' => $apiRequest->ticket_id, 'api_request_id' => $apiRequest->id, 'attempt' => $apiRequest->attempt, 'exception' => $exception::class, 'message' => $exception->getMessage(),]);
    }
}
