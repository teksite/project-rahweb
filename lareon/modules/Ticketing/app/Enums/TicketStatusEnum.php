<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum TicketStatusEnum: int
{
    case PENDING = 0;
    case IN_REVIEW =1;
    case APPROVED = 2;
    case REJECTED = 3;
}
