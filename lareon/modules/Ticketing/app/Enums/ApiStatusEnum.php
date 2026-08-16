<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum ApiStatusEnum: int
{
    case PENDING = 0;
    case PROCESSING = 1;
    case SUCCESS = 2;

    case FAILED = 3;


}
