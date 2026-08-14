<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum TicketStatusEnum: int
{
    case PENDING = 0;
    case IN_REVIEW =1;
    case APPROVED = 2;
    case REJECTED = 3;

    public function label(): string
    {
        return match ($this) {
            TicketStatusEnum::PENDING => __('Pending'),
            TicketStatusEnum::IN_REVIEW => __('In Review'),
            TicketStatusEnum::APPROVED => __('Approved'),
            TicketStatusEnum::REJECTED => __('Rejected'),

        };
    }
}
