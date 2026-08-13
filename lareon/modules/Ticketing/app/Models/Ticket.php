<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;

#[Fillable('title', 'body', 'file', 'status',)]
class Ticket extends Model
{
    protected function casts(): array
    {
        return [
            'status'=>TicketStatusEnum::class,
        ];
    }
}
