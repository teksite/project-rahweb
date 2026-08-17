<?php

namespace Lareon\Modules\Ticketing\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lareon\Modules\Ticketing\App\Models\Ticket;

class NewTicketNotificationToAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     *  Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject("new Ticket #{$this->ticket->id}")
                                ->greeting('hi')
                                ->line('a new ticket is submitted in the system')
                                ->line("**ticket ID:** #{$this->ticket->id}")
                                ->line("**title:** {$this->ticket->title}")
                                ->line("**description:** {$this->ticket->body}")
                                ->when($this->ticket->user, fn(MailMessage $mail) => $mail->line("**creator:** {$this->ticket->creator->name}"))
                                ->line('please, check the ticket and do what is necessary')
                                ->action('visit', route('admin.tickets.edit', $this->ticket));
    }

    /** * Get the array representation of the notification. */
    public function toArray(object $notifiable): array
    {
        return ['ticket_id' => $this->ticket->id, 'title' => $this->ticket->title, 'user_id' => $this->ticket->user_id,];
    }
}
