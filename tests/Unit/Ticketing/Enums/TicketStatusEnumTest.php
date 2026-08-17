<?php

namespace Tests\Unit\Ticketing\Enums;

use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Tests\TestCase;

class TicketStatusEnumTest extends TestCase
{
    public function test_values_are_correct(): void
    {
        $this->assertSame(
            0,
            TicketStatusEnum::IN_REVIEW->value
        );

        $this->assertSame(
            1,
            TicketStatusEnum::APPROVED->value
        );

        $this->assertSame(
            2,
            TicketStatusEnum::REJECTED->value
        );

        $this->assertSame(
            4,
            TicketStatusEnum::PENDING->value
        );
    }

    public function test_keys_are_correct(): void
    {
        $this->assertSame(
            'in_review',
            TicketStatusEnum::IN_REVIEW->key()
        );

        $this->assertSame(
            'approved',
            TicketStatusEnum::APPROVED->key()
        );

        $this->assertSame(
            'rejected',
            TicketStatusEnum::REJECTED->key()
        );

        $this->assertSame(
            'pending',
            TicketStatusEnum::PENDING->key()
        );
    }

    public function test_badge_classes_are_defined(): void
    {
        foreach (TicketStatusEnum::cases() as $status) {
            $this->assertNotEmpty(
                $status->badgeClasses()
            );
        }
    }

    public function test_html_contains_label_and_classes(): void
    {
        foreach (TicketStatusEnum::cases() as $status) {
            $html = $status->toHtml();

            $this->assertStringContainsString(
                $status->label(),
                $html
            );

            $this->assertStringContainsString(
                $status->badgeClasses(),
                $html
            );
        }
    }
}
