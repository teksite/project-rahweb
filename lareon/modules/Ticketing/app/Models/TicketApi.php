<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\ApiStatusEnum;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\User\App\Models\User;

#[Table('tickets_api_requests')]
#[Fillable('ticket_id', 'idempotency_key', 'attempt', 'status', 'request_id', 'response_code', 'response_body', 'error_message', 'sent_at' ,'completed_at')]
class TicketApi extends Model
{

    protected function casts(): array
    {
        return [
            'status'=>ApiStatusEnum::class,
            'error_message'=>'json',
            'response_body'=>'json',
        ];
    }

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

//    public function approvals()
//    {
//        return $this->hasManyThrough(Ticket::class ,TicketApproval::class , 'ticket_id', 'ticket_id', 'id', 'id');
//    }

}
