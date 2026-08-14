<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum TicketStatusEnum: int
{
    case IN_REVIEW =0;
    case APPROVED = 1;
    case REJECTED = 2;

    case PENDING  = 4;

    public function label(): string
    {
        return match ($this) {
            TicketStatusEnum::IN_REVIEW => __('In Review'),
            TicketStatusEnum::APPROVED => __('Approved'),
            TicketStatusEnum::REJECTED => __('Rejected'),
            default => __('Pending'),

        };
    }


    public function key(): string
    {
        return match ($this) {
            self::IN_REVIEW => 'in_review',
            self::APPROVED   => 'approved',
            self::REJECTED   => 'rejected',
            default => __('pending'),

        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::IN_REVIEW => 'text-orange-600 bg-orange-100',
            self::APPROVED => 'text-green-600 bg-green-100',
            self::REJECTED   => 'text-red-600 bg-red-100',
            default   => 'text-yellow-600 bg-yellow-100',
        };
    }


    public function toHtml(): string
    {
        return sprintf(
            "<span class='%s font-bold text-xs px-3 py-1 rounded-xl select-none'>%s</span>",
            $this->badgeClasses(),
            e($this->label())
        );
    }
}
