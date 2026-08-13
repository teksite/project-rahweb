<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum ApiStatusEnum: int
{
    case NOT_SENT  = 0;
    case SENDING = 1;
    case SENT = 2;
    case Retry_SENDING = 3;
    case API_FAILED = 4;

}
