<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\User\App\Models\User;

#[Fillable('title', 'body', 'file', 'user_id')]
class Ticket extends Model
{
    public function approvals()
    {
        return $this->hasMany(TicketApprovals::class, 'ticket_id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    protected function status(): Attribute
    {
        if ($this->approvals()->count()) {
            if ($this->approvals()->where('status', TicketStatusEnum::REJECTED->value)->count()) {
                $status = TicketStatusEnum::REJECTED;
            } elseif ($this->approvals()->where('status', TicketStatusEnum::APPROVED->value)->count() === 2) {
                $status = TicketStatusEnum::APPROVED;
            } else {
                $status = TicketStatusEnum::IN_REVIEW;
            }
        } else {
            $status = TicketStatusEnum::PENDING;
        }


        return Attribute::make(
            get: fn(mixed $value, array $attributes) => $status,
        );
    }
}
