<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApi;
use Teksite\Handler\Actions\ServiceWrapper;
use Teksite\Handler\contracts\ServiceResult;
use Teksite\Handler\Services\FetchDataService;


class RequestsLogic
{
    /**
     * @throws \Throwable
     */
    public function all(mixed $fetchData = []): ServiceResult
    {

        return ServiceWrapper::make(false)
                             ->do(
                                 fn() => FetchDataService::get(TicketApi::class, ['status', 'ticket.title'], with: ['ticket'])
                             )->run();
    }


    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function first(array $inputs = [], bool $any = true): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($inputs) {
            $query = TicketApi::query();
            foreach ($inputs as $key => $value) {
                $query->where($key, $value);
            }
        })->run();
    }



    /**
     * @throws \Throwable
     */
    public function delete(TicketApi $req): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($req) {
            $req->delete();
        })->run();
    }

}

