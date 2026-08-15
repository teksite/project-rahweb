<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class ApprovalTicketLogic
{
    public function firstOrCreate(Ticket $ticket)
    {

        $user = $this->getUser();

        if (!$user) return new \Teksite\Handler\Actions\ServiceResult(false, null);

        return ServiceWrapper::make(false)->do(function () use ($ticket, $user) {
            return TicketApprovals::query()->firstOrCreate([
                'ticket_id' => $ticket->id,
                'admin_id'  => $user->id,
                'role_id'   => $user->roles()->first()->id,
            ], [
                'status' => TicketStatusEnum::IN_REVIEW->value,
            ]);
        });

    }

    /**
     * @throws \Throwable
     */
    public function update(Ticket $ticket, array $inputs = []): ServiceResult
    {
        $user = $this->getUser();

        if (!$user) abort(403);

        return ServiceWrapper::make(true)->do(function () use ($inputs, $ticket) {
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

    public function getUser(): \Lareon\Modules\User\App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        $user = auth()->user();
        return $user->hasRole(['chief ticket manager', 'ticket manager']) ? $user : null;
    }

}

