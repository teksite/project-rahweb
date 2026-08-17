<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Observers\TicketApprovalObserver;
use Lareon\Modules\Ticketing\Database\Factories\TicketApprovalFactory;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;

#[Fillable('ticket_id', 'admin_id', 'role_id', 'round', 'status', 'review',)]
#[Table('tickets_approvals')]
#[ObservedBy([TicketApprovalObserver::class])]
#[UseFactory(TicketApprovalFactory::class)]

class TicketApproval extends Model
{
    /** @use HasFactory<TicketApprovalFactory> */
    use HasFactory;
    protected function casts(): array
    {
        return [
            'status' => TicketStatusEnum::class,
        ];
    }


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }


    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }


}
