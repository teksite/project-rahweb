<?php

namespace Tests\Unit\Ticketing\Enums;

use Lareon\Modules\Ticketing\App\Enums\ApiStatusEnum;
use Tests\TestCase;

class ApiStatusEnumTest extends TestCase
{
    public function test_all_status_values_are_correct(): void
    {
        $this->assertSame(0, ApiStatusEnum::PENDING->value);
        $this->assertSame(1, ApiStatusEnum::PROCESSING->value);
        $this->assertSame(2, ApiStatusEnum::SUCCESS->value);
        $this->assertSame(3, ApiStatusEnum::FAILED->value);
    }

    public function test_labels_are_correct(): void
    {
        $this->assertSame(
            __('pending'),
            ApiStatusEnum::PENDING->label()
        );

        $this->assertSame(
            __('processing'),
            ApiStatusEnum::PROCESSING->label()
        );

        $this->assertSame(
            __('success'),
            ApiStatusEnum::SUCCESS->label()
        );

        $this->assertSame(
            __('failed'),
            ApiStatusEnum::FAILED->label()
        );
    }
}
