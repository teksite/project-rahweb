<?php

namespace Lareon\Modules\Ticketing\App\queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Teksite\Authorize\Models\Role;

class TicketListQuery
{
    protected ?int $ticketManagerRoleId = null;

    protected ?int $chiefTicketManagerRoleId = null;

    public function paginate(): LengthAwarePaginator
    {
        $query = Ticket::query();

        $this->applyVisibility($query);
        $this->applySearch($query);

        return $query->with('approvals')->paginate();
    }


    protected function applyVisibility(Builder $query): void
    {
        $user = auth()->user();

        if ($user->hasRole('ticket manager')) {
            $this->applyTicketManagerVisibility($query);
            return;
        }

        if ($user->hasRole('chief ticket manager')) {
            $this->applyChiefTicketManagerVisibility($query);
        }
    }

    protected function applyTicketManagerVisibility(Builder $query): void
    {
        $user = auth()->user();

        $ticketManagerRoleId = $this->ticketManagerRoleId();
        $chiefTicketManagerRoleId = $this->chiefTicketManagerRoleId();

        $query->where(function (Builder $query) use ($user, $ticketManagerRoleId, $chiefTicketManagerRoleId) {

            $query->whereDoesntHave('approvals');

            $query->orWhere(function (Builder $query) use ($user, $ticketManagerRoleId, $chiefTicketManagerRoleId) {

                $query->whereHas('approvals', function (Builder $query) use ($user, $ticketManagerRoleId) {
                    $query->where('role_id', $ticketManagerRoleId)->where('admin_id', $user->id);
                })->whereDoesntHave('approvals', function (Builder $query) use ($chiefTicketManagerRoleId) {
                    $query->where('role_id', $chiefTicketManagerRoleId);
                });
            });
        });
    }

    protected function applyChiefTicketManagerVisibility(Builder $query): void
    {
        $user = auth()->user();

        $ticketManagerRoleId = $this->ticketManagerRoleId();
        $chiefTicketManagerRoleId = $this->chiefTicketManagerRoleId();


        $query->whereHas('approvals', function (Builder $query) use ($ticketManagerRoleId) {
            $query->where('role_id', $ticketManagerRoleId)->where('status', TicketStatusEnum::APPROVED->value);
        });

        $query->where(function (Builder $query) use ($user, $chiefTicketManagerRoleId) {

            $query->whereDoesntHave('approvals', function (Builder $query) use ($chiefTicketManagerRoleId) {
                    $query->where('role_id', $chiefTicketManagerRoleId);
                })->orWhereHas('approvals', function (Builder $query) use ($user, $chiefTicketManagerRoleId) {
                    $query->where('role_id', $chiefTicketManagerRoleId)->where('admin_id', $user->id);
                });
        });
    }


    protected function applySearch(Builder $query): void
    {
        $search = trim(request('s', ''));

        if ($search === '')  return;


        $query->where(function (Builder $query) use ($search) {

            $this->searchTitle($query, $search);

            $this->searchCreator($query, $search);

            $this->searchApprovalStatus($query, $search);
        });
    }

    protected function searchTitle(Builder $query, string  $search): void
    {
        $query->where('title', 'like', "%{$search}%");
    }

    protected function searchCreator(Builder $query, string  $search): void
    {
        $query->orWhereHas('creator', function (Builder $query) use ($search) {

            $query->where('name', 'like', "%{$search}%")->orWhere('lastname', 'like', "%{$search}%");
        });
    }

    protected function searchApprovalStatus(Builder $query, string  $search): void
    {
        $status = $this->resolveStatus($search);

        if ($status === null)  return;


        $query->orWhereHas('approvals', function (Builder $query) use ($status) {
            $query->where('status', $status);
        });
    }

    protected function resolveStatus(string $search): ?int
    {
        return match (strtolower($search)) {
            'pending'   => TicketStatusEnum::PENDING->value,
            'approved'  => TicketStatusEnum::APPROVED->value,
            'rejected'  => TicketStatusEnum::REJECTED->value,
            'in_review' => TicketStatusEnum::IN_REVIEW->value,
            default     => null,
        };
    }


    protected function ticketManagerRoleId(): int
    {
        return $this->ticketManagerRoleId ??= Role::query()->where('title', 'ticket manager')->value('id');
    }

    protected function chiefTicketManagerRoleId(): int
    {
        return $this->chiefTicketManagerRoleId ??= Role::query()->where('title', 'chief ticket manager')->value('id');
    }
}
