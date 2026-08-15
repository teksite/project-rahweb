<?php

namespace Lareon\Modules\Ticketing\App\Observers;

use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;

class TicketApprovalObserver
{
    public function updated(TicketApproval $approval): void
    {
        if (!$approval->wasChanged('status')) {
            return;
        }

        if ($approval->status !== TicketStatusEnum::APPROVED) {
            return;
        }

        if ($approval->role_id === $this->ticketManagerRoleId()) {
            TicketApprovedByManager::dispatch($approval);

            return;
        }

        if ($approval->role_id === $this->chiefTicketManagerRoleId()) {
            TicketApprovedByChief::dispatch($approval);
        }
    }
}
