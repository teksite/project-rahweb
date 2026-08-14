<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;

#[Fillable('ticket_id', 'admin_id', 'role_id', 'round', 'status', 'review',)]
#[Table('tickets_approvals')]
class TicketApprovals extends Model
{
    protected function casts(): array
    {
        return [
            'status' => TicketStatusEnum::class,
        ];
    }


    public function tickets(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }


}
