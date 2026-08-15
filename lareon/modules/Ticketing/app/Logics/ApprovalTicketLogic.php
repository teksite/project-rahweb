<?php

namespace Lareon\Modules\Ticketing\App\Logics;


use Lareon\Modules\Ticketing\App\Action\TicketBulkAction;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Teksite\Authorize\Models\Role;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class ApprovalTicketLogic
{
    public function prepareApproval(Ticket $ticket)
    {
        $user = $this->getUser();

        if ($user === null) {
            return new \Teksite\Handler\Actions\ServiceResult(false, null);
        }
        return ServiceWrapper::make(false)->do(function () use ($ticket, $user) {

            return TicketApproval::query()->firstOrCreate([
                'ticket_id' => $ticket->id,
                'admin_id'  => $user->id,
                'role_id'   => $user->roles()->first()->id,
            ], [
                'status' => TicketStatusEnum::IN_REVIEW->value,
            ]);
        })->run();

    }

    /**
     * @throws \Throwable
     */
    public function update(Ticket $ticket, array $inputs = []): ServiceResult
    {
        $user = $this->getUser();
        if ($user === null) abort(403);


        return ServiceWrapper::make(false)->do(function () use ($inputs, $ticket) {
            $user = auth()->user();
            $userId = $user->id;
            $roleId = $user->roles()->first()->id;
            $approval = $ticket->approvals()->updateOrCreate(
                [
                    'admin_id' => $userId,
                    'role_id'  => $roleId,
                ], [
                    'review' => $inputs['review'],
                    'status' => $inputs['status'],
                ]

            );
            return $approval->refresh();
        })->run();
    }


    public function bulkAction(string $action)
    {
        return ServiceWrapper::make(false)->do(function () use ($action) {
            return app(TicketBulkAction::class)->handle($action);

        })->run();

    }

    public function getUser(): \Lareon\Modules\User\App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        $user = auth()->user();
        return $user->hasRole(['chief ticket manager', 'ticket manager']) ? $user : null;
    }

}

