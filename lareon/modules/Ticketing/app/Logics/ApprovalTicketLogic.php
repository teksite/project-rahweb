<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Auth\Authenticatable;
use Lareon\Modules\Ticketing\App\Action\TicketBulkAction;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Teksite\Handler\Actions\ServiceResult;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult as ServiceResultContract;

class ApprovalTicketLogic
{
    public function prepareApproval(Ticket $ticket): ServiceResultContract
    {
        $user = $this->getUser();

        if ($user === null) return new ServiceResult(false, null);


        return ServiceWrapper::make(false)
                             ->do(function () use ($ticket, $user) {
                                 $roleId = $user->roles()->first()?->id;

                                 if (!$roleId) {
                                     return null;
                                 }

                                 return TicketApproval::query()->firstOrCreate(
                                     [
                                         'ticket_id' => $ticket->id,
                                         'admin_id'  => $user->id,
                                         'role_id'   => $roleId,
                                         'round'     => 1,
                                     ],
                                     [
                                         'status' => TicketStatusEnum::IN_REVIEW,
                                     ]
                                 );
                             })
                             ->run();
    }

    public function update(Ticket $ticket, array $inputs = []): ServiceResultContract
    {
        $user = $this->getUser();

        if ($user === null) abort(403);


        return ServiceWrapper::make(false)->do(function () use ($inputs, $ticket, $user) {
            $roleId = $user->roles()->first()?->id;
            if (!$roleId) abort(403);

            return $ticket->approvals()->updateOrCreate(
                [
                    'admin_id' => $user->id,
                    'role_id'  => $roleId,
                    'round'    => 1,
                ],
                [
                    'review' => $inputs['review'] ?? null,
                    'status' => $inputs['status'],
                ]
            )->refresh();
        })->run();
    }

    public function bulkAction(string $action): ServiceResultContract
    {
        return ServiceWrapper::make(false)->do(
            fn() => app(TicketBulkAction::class)
                ->handle($action)
        )->run();
    }

    public function getUser(): ?Authenticatable
    {
        $user = auth()->user();

        if (!$user) return null;


        return $user->hasRole(['chief ticket manager', 'ticket manager',]) ? $user : null;
    }
}
