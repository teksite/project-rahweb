<?php

namespace Lareon\Modules\Ticketing\App\Action;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use Teksite\Authorize\Models\Role;

class TicketBulkAction
{
    private const string REVIEW = 'review';
    private const string APPROVE = 'approve';
    private const string REJECT = 'reject';

    private const string TICKET_MANAGER = 'ticket manager';
    private const string CHIEF_TICKET_MANAGER = 'chief ticket manager';

    private const int CHUNK_SIZE = 500;

    protected ?int $ticketManagerRoleId = null;

    protected ?int $chiefTicketManagerRoleId = null;

    public function handle(string $action): int
    {
        $this->validateAction($action);

        $user = auth()->user();

        if ($user->hasRole(self::TICKET_MANAGER)) {
            return $this->handleAsTicketManager(
                $action,
                $user->id
            );
        }

        if ($user->hasRole(self::CHIEF_TICKET_MANAGER)) {
            return $this->handleAsChief(
                $action,
                $user->id
            );
        }

        throw new InvalidArgumentException(
            'You are not allowed to perform ticket bulk actions.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ticket Manager
    |--------------------------------------------------------------------------
    */

    protected function handleAsTicketManager(
        string $action,
        int $userId
    ): int {
        return match ($action) {
            self::REVIEW => $this->reviewAsTicketManager($userId),

            self::APPROVE => $this->changeOwnApprovalStatus(
                userId: $userId,
                roleId: $this->ticketManagerRoleId(),
                status: TicketStatusEnum::APPROVED,
            ),

            self::REJECT => $this->changeOwnApprovalStatus(
                userId: $userId,
                roleId: $this->ticketManagerRoleId(),
                status: TicketStatusEnum::REJECTED,
            ),
        };
    }

    protected function reviewAsTicketManager(int $userId): int
    {
        $query = Ticket::query()
                       ->whereDoesntHave('apiRequests')
                       ->whereDoesntHave('approvals');

        return $this->createApprovals(
            query: $query,
            userId: $userId,
            roleId: $this->ticketManagerRoleId(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Chief Ticket Manager
    |--------------------------------------------------------------------------
    */

    protected function handleAsChief(
        string $action,
        int $userId
    ): int {
        return match ($action) {
            self::REVIEW => $this->reviewAsChief($userId),

            self::APPROVE => $this->changeOwnApprovalStatus(
                userId: $userId,
                roleId: $this->chiefTicketManagerRoleId(),
                status: TicketStatusEnum::APPROVED,
            ),

            self::REJECT => $this->changeOwnApprovalStatus(
                userId: $userId,
                roleId: $this->chiefTicketManagerRoleId(),
                status: TicketStatusEnum::REJECTED,
            ),
        };
    }

    protected function reviewAsChief(int $userId): int
    {
        $managerRoleId = $this->ticketManagerRoleId();
        $chiefRoleId = $this->chiefTicketManagerRoleId();

        $query = Ticket::query()
                       ->whereDoesntHave('apiRequests')

            // Manager 1 باید approve کرده باشد
                       ->whereHas('approvals', function (Builder $query) use (
                $managerRoleId
            ) {
                $query
                    ->where('role_id', $managerRoleId)
                    ->where(
                        'status',
                        TicketStatusEnum::APPROVED->value
                    );
            })

            // Chief هنوز approval نداشته باشد
                       ->whereDoesntHave('approvals', function (Builder $query) use (
                $chiefRoleId
            ) {
                $query->where('role_id', $chiefRoleId);
            });

        return $this->createApprovals(
            query: $query,
            userId: $userId,
            roleId: $chiefRoleId,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve / Reject
    |--------------------------------------------------------------------------
    */

    protected function changeOwnApprovalStatus(
        int $userId,
        int $roleId,
        TicketStatusEnum $status
    ): int {
        $count = 0;

        TicketApproval::query()
                      ->where('admin_id', $userId)
                      ->where('role_id', $roleId)

            // فقط approvalهایی که در review هستند
                      ->where(
                'status',
                TicketStatusEnum::IN_REVIEW->value
            )

            // Ticket نباید وارد API شده باشد
                      ->whereHas('ticket', function (Builder $query) {
                $query->whereDoesntHave('apiRequests');
            })

                      ->chunkById(
                          self::CHUNK_SIZE,
                          function ($approvals) use (
                              $status,
                              &$count
                          ) {
                              foreach ($approvals as $approval) {
                                  $approval->status = $status;

                                  if (! $approval->isDirty('status')) {
                                      continue;
                                  }

                                  $approval->save();

                                  $count++;
                              }
                          }
                      );

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Create Approvals
    |--------------------------------------------------------------------------
    */

    protected function createApprovals(
        Builder $query,
        int $userId,
        int $roleId
    ): int {
        $count = 0;

        $query->chunkById(
            self::CHUNK_SIZE,
            function ($tickets) use (
                $userId,
                $roleId,
                &$count
            ) {
                $now = now();

                $rows = $tickets
                    ->map(fn (Ticket $ticket) => [
                        'ticket_id' => $ticket->id,
                        'admin_id' => $userId,
                        'role_id' => $roleId,
                        'round' => 1,
                        'status' => TicketStatusEnum::IN_REVIEW->value,
                        'review' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                if (empty($rows)) {
                    return;
                }

                $count += TicketApproval::query()
                                        ->insertOrIgnore($rows);
            }
        );

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function validateAction(string $action): void
    {
        if (! in_array($action, [
            self::REVIEW,
            self::APPROVE,
            self::REJECT,
        ], true)) {
            throw new InvalidArgumentException(
                "Invalid ticket bulk action [{$action}]."
            );
        }
    }

    protected function ticketManagerRoleId(): int
    {
        return $this->ticketManagerRoleId ??= Role::query()
                                                  ->where('title', self::TICKET_MANAGER)
                                                  ->value('id');
    }

    protected function chiefTicketManagerRoleId(): int
    {
        return $this->chiefTicketManagerRoleId ??= Role::query()
                                                       ->where('title', self::CHIEF_TICKET_MANAGER)
                                                       ->value('id');
    }
}
