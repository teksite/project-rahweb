<?php

namespace Lareon\Modules\Ticketing\App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\Database\Factories\TicketFactory;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;

#[UseFactory(TicketFactory::class)]
#[Fillable('title', 'body', 'file', 'user_id')]
class Ticket extends Model
{
    use HasFactory;

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): TicketStatusEnum {
                $approvals = $this->relationLoaded('approvals')
                    ? $this->approvals
                    : $this->approvals()->get();

                if ($approvals->isEmpty()) {
                    return TicketStatusEnum::PENDING;
                }

                if ($approvals->contains(
                    fn (TicketApproval $approval) =>
                        $approval->status === TicketStatusEnum::REJECTED
                )) {
                    return TicketStatusEnum::REJECTED;
                }

                if ($approvals->where(
                        'status',
                        TicketStatusEnum::APPROVED
                    )->count() >= 2) {
                    return TicketStatusEnum::APPROVED;
                }

                return TicketStatusEnum::IN_REVIEW;
            },
        );
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TicketApproval::class, 'ticket_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function apiRequests(): HasMany
    {
        return $this->hasMany(TicketApi::class, 'ticket_id');
    }

    public function approvementByFirstAdmin(): ?TicketApproval
    {
        $roleId = Role::query()
                      ->where('title', 'ticket manager')
                      ->value('id');

        if (!$roleId) return null;

        return $this->approvals()
                    ->where('status', TicketStatusEnum::APPROVED)
                    ->where('role_id', $roleId)
                    ->first();
    }

    public function approvementBySecondAdmin(): ?TicketApproval
    {
        $roleId = Role::query()
                      ->where('title', 'chief ticket manager')
                      ->value('id');

        if (!$roleId)  return null;

        return $this->approvals()
                    ->where('status', TicketStatusEnum::APPROVED)
                    ->where('role_id', $roleId)
                    ->first();
    }
}
