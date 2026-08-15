<?php

namespace Lareon\Modules\Ticketing\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;

class UpdateTicketStatusToClient extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket, public TicketApproval $approval)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification delivery channels.
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
        $approval = $this->approval;

        $manager = $approval->role?->title ?? 'Ticket Manager';

        $status = $approval->status;

        $isApproved = $status === TicketStatusEnum::APPROVED;
        $isRejected = $status === TicketStatusEnum::REJECTED;

        return (new MailMessage)
            ->subject($isApproved ? "Ticket #{$this->ticket->id} approved" : "Ticket #{$this->ticket->id} rejected")
            ->greeting('Hello,')
            ->line("The status of your ticket has been updated by {$manager}.")
            ->line("**Ticket ID:** #{$this->ticket->id}")
            ->line("**Ticket title:** {$this->ticket->title}")
            ->line("**Status:** {$status->label()}")
            ->when(
                $isApproved && $this->isWaitingForNextApproval($manager),
                fn(MailMessage $mail) => $mail->line('Your ticket has been approved by this manager and is now waiting for the next manager approval.')
            )
            ->when(
                $isApproved && $this->isFinalApproval($manager),
                fn(MailMessage $mail) => $mail->line('Your ticket has been fully approved.')
            )
            ->when(
                $isRejected,
                fn(MailMessage $mail) => $mail->line($approval->review ? "Reason for rejection: {$approval->review}" : 'Your ticket has been rejected.')
            )
            ->line('Thank you for using our application.');
    }

    /**
     * Determine whether the ticket is waiting
     * for the next manager approval.
     */
    protected function isWaitingForNextApproval(string $manager): bool
    {
        return $manager !== 'chief ticket manager';
    }

    /**
     * Determine whether this is the final approval.
     */
    protected function isFinalApproval(string $manager): bool
    {
        return $manager === 'chief ticket manager';
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id'    => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'approval_id'  => $this->approval->id,
            'status'       => $this->approval->status->value,
            'role'         => $this->approval->role?->title,
            'review'       => $this->approval->review,
            'user_id'      => $this->ticket->user_id,
        ];
    }
}

