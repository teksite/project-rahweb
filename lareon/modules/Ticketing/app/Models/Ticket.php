<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\User\App\Models\User;

#[Fillable('title', 'body', 'file', 'status','user_id')]
class Ticket extends Model
{
    protected function casts(): array
    {
        return [
            'status'=>TicketStatusEnum::class,
        ];
    }

    public function approvals()
    {
        return $this->hasMany(TicketApprovals::class , 'ticket_id');
    }



    public function creator()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
