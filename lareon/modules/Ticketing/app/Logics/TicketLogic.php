<?php

namespace Lareon\Modules\Ticketing\App\Logics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lareon\Modules\Ticketing\App\Models\Ticket;
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
                             ->do(fn() => FetchDataService::get(Ticket::class, ['title',]))
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
        return ServiceWrapper::make(true)->do(function () use ($inputs) {
            $inputs['slug'] ??= strtolower(uniqid() . '-' . Str::random(4));
            $inputs['parent_id'] = auth()->id();
            $ticket = Ticket::create($inputs);
            $rolesIds = $this->assignRole($ticket, config('general.default_user_role', 'user'));
            return $ticket;
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function update(Ticket $ticket, array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($ticket, $inputs) {
            if (!isset($inputs['password']) || $inputs['password'] === null)  unset($inputs['password']);

            $ticket->fill(Arr::except($inputs, ['permissions', 'roles', 'enable_2fa', 'meta', 'seo']));
            $this->toggle2fa($ticket, $inputs['enable_2fa'] ?? null);
            $ticket->save();
            return $ticket->refresh();
        })->run();
    }


    /**
     * @throws \Throwable
     */
    public function changePassword(Ticket $ticket, array $inputs = []): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($ticket, $inputs) {
            $ticket->update(['password' =>$inputs['password']]);
        })->run();
    }

    /**
     * @throws \Throwable
     */
    public function delete(Ticket $ticket): ServiceResult
    {
        return ServiceWrapper::make(false)->do(function () use ($ticket) {
            $ticket->roles()->detach();
            $ticket->delete();
        })->run();
    }



}

