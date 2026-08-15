<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\User\App\Models\User;

#[Table('tickets_api_requests')]
#[Fillable('ticket_id', 'idempotency_key', 'attempt', 'status', 'request_id', 'response_code', 'response_body', 'error_message', 'sent_at' ,'completed_at')]
class TicketApi extends Model
{

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->hasManyThrough(TicketApproval::class, Ticket::class, 'ticket_id', 'ticket_id', 'id', 'id');
    }

}
