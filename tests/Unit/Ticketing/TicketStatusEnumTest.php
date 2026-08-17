<?php

namespace Tests\Unit\Ticketing;

use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Tests\TestCase;
class TicketStatusEnumTest extends TestCase
{
    public function test_pending_status(): void
    {
        $status = TicketStatusEnum::PENDING;

        $this->assertSame(4, $status->value);
        $this->assertSame('pending', $status->key());
    }

    public function test_in_review_status(): void
    {
        $status = TicketStatusEnum::IN_REVIEW;

        $this->assertSame(0, $status->value);
        $this->assertSame('in_review', $status->key());
    }

    public function test_approved_status(): void
    {
        $status = TicketStatusEnum::APPROVED;

        $this->assertSame(1, $status->value);
        $this->assertSame('approved', $status->key());
    }

    public function test_rejected_status(): void
    {
        $status = TicketStatusEnum::REJECTED;

        $this->assertSame(2, $status->value);
        $this->assertSame('rejected', $status->key());
    }

    public function test_approved_status_generates_html(): void
    {
        $html = TicketStatusEnum::APPROVED->toHtml();

        $this->assertStringContainsString(
            'Approved',
            $html
        );

        $this->assertStringContainsString(
            '<span',
            $html
        );
    }
}
