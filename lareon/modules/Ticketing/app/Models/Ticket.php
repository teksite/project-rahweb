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
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): TicketStatusEnum {
                $approvals = $this->relationLoaded('approvals')
                    ? $this->approvals
                    : $this->approvals()->get();

                if ($approvals->isEmpty()) return TicketStatusEnum::PENDING;


                if ($approvals->contains(fn ($approval) => $approval->status === TicketStatusEnum::REJECTED->value)) {
                    return TicketStatusEnum::REJECTED;
                }

                if ($approvals->where('status', TicketStatusEnum::APPROVED->value)->count() >= 2) {
                    return TicketStatusEnum::APPROVED;
                }

                return TicketStatusEnum::IN_REVIEW;
            },
        );
    }



    public function approvals()
    {
        return $this->hasMany(TicketApprovals::class, 'ticket_id');
    }


    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }





    public function apiRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketApi::class, 'ticket_id');
    }
}
