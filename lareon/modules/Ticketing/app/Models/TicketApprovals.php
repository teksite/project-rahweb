<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;

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


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }


    public function tickets(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }


}
