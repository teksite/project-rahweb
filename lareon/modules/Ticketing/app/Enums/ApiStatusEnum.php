<?php

namespace Lareon\Modules\Ticketing\App\Enums;

enum ApiStatusEnum: int
{
    case PENDING = 0;
    case PROCESSING = 1;
    case SUCCESS = 2;

    case FAILED = 3;

    public function label(): string
    {
        return match ($this) {
            ApiStatusEnum::PENDING => __('pending'),
            ApiStatusEnum::PROCESSING => __('processing'),
            ApiStatusEnum::SUCCESS => __('success'),
            ApiStatusEnum::FAILED => __('failed'),
            default => __('unknown'),

        };
    }
}
