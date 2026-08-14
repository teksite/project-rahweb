<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\queries\TicketListQuery;
use Lareon\Modules\Ticketing\App\Services\UploadFileService;
use Teksite\Authorize\Models\Role;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class TicketLogic
{
    /**
     * @throws \Throwable
     */
    public function all(mixed $fetchData = []): ServiceResult
    {

        return ServiceWrapper::make(false)
                             ->do(fn() => app(TicketListQuery::class)->paginate())
                             ->run();

    }

    /**
     * @throws \Throwable
     */
    public function allByUser(mixed $fetchData = []): ServiceResult
    {
        return ServiceWrapper::make(false)
                             ->do(fn() => FetchDataService::get(auth()->user()->tickets(), ['title']))
                             ->run();
    }


    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function first(array $inputs = [], bool $any = true): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($inputs) {
            $query = Ticket::query();
            foreach ($inputs as $key => $value) {
                $query->where($key, $value);
            }
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function create(array $inputs = []): ServiceResult
    {
        $userId = auth()->id();
        $file = (new UploadFileService())->store($inputs['file'], $userId);

        return ServiceWrapper::make(true)->do(function () use ($userId, $inputs, $file) {
            return Ticket::query()->create([
                'user_id' => $userId,
                'title'   => $inputs['title'],
                'body'    => $inputs['body'],
                'file'    => $file,
            ]);
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

