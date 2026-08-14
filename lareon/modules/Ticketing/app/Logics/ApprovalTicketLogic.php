<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Services\UploadFileService;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class ApprovalTicketLogic
{
    /**
     * @throws \Throwable
     */
    public function all(mixed $fetchData = []): ServiceResult
    {
        return ServiceWrapper::make(false)
                             ->do(fn() => FetchDataService::get(Ticket::class, ['title',]))
                             ->run();
    }


    /**
     * @throws \Throwable
     */
    public function update(Ticket $ticket, array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(true)->do(function () use ($inputs, $ticket) {
            $user = auth()->user();
            $userId = $user->id;
            $roleId = $user->roles()->first()->id;
            $approval = $ticket->approvals()->updateOrCreate(
                [
                    'admin_id' => $userId,
                    'role_id' => $roleId,
                ], [
                    'review' => $inputs['review'],
                    'status' => $inputs['status'],
                ]

            );
            return $approval->refresh();
        })->run();
    }


    /**
     * @throws \Throwable
     */
    public function delete(Ticket $ticket): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($ticket) {
            $ticket->delete();
        })->run();
    }

}

