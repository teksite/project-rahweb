<?php

namespace Lareon\Modules\Ticketing\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;

class UpdateTicketStatusToAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the notification may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket, public TicketApproval $approval,) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id} requires your approval")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new ticket is waiting for your review.')
            ->line('The first ticket manager has approved this ticket, and it has now been forwarded to you for the next approval step.')
            ->line("**Ticket ID:** #{$this->ticket->id}")
            ->line("**Ticket title:** {$this->ticket->title}")
            ->line('Please review the ticket and either approve or reject it.')
            ->action('Review Ticket', route('admin.tickets.edit', $this->ticket))
            ->line('If you reject the ticket, please provide a reason for the rejection.')

            ->line('Thank you.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'approval_id' => $this->approval->id,
            'user_id' => $this->ticket->user_id,
        ];
    }
}

