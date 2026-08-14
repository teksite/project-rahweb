<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\NewTicketRequest;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\UpdateApprovalTicketRequest;
use Lareon\Modules\Ticketing\App\Logics\ApprovalTicketLogic;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Teksite\Authorize\Models\Role;
use Teksite\Handler\Facade\Responder;

class TicketsController extends Controller implements HasMiddleware
{

    public function __construct(public TicketLogic $logic, public ApprovalTicketLogic $approvalLogic) {}

    public static function middleware()
    {
        return [
            new Middleware('can:admin.ticket.read'),
            new Middleware('can:admin.ticket.edit', only: ['edit', 'update']),
            new Middleware('can:admin.ticket.delete', only: ['destroy']),
        ];
    }

    /**
     * @throws \Throwable
     */
    public function index()
    {
        $user = auth()->user();

        $chiefTicketManagerRoleId = Role::query()->where('title', 'chief ticket manager')->value('id');
        $ticketManagerRoleId = Role::query()->where('title', 'ticket manager')->value('id');

        $query = Ticket::query();

        if ($user->hasRole('ticket manager')) {

            $query->where(function ($query) use ($ticketManagerRoleId, $chiefTicketManagerRoleId, $user) {

                $query->whereDoesntHave('approvals')
                      ->orWhere(function ($query) use ($ticketManagerRoleId, $chiefTicketManagerRoleId, $user) {
                          $query
                              ->whereHas('approvals', function ($query) use ($ticketManagerRoleId, $user) {
                                  $query->where('role_id', $ticketManagerRoleId)->where('admin_id', $user->id);
                              })->whereDoesntHave('approvals', function ($query) use ($chiefTicketManagerRoleId) {
                                  $query->where('role_id', $chiefTicketManagerRoleId);
                              });
                      });
            });

        } elseif ($user->hasRole('chief ticket manager')) {

            $query->whereHas('approvals', function ($query) use ($ticketManagerRoleId) {
                $query->where('role_id', $ticketManagerRoleId)->where('status', TicketStatusEnum::APPROVED->value);
            })->where(function ($query) use ($chiefTicketManagerRoleId, $user) {
                $query
                    ->whereDoesntHave('approvals', function ($query) use ($chiefTicketManagerRoleId) {
                        $query->where('role_id', $chiefTicketManagerRoleId);
                    })
                    ->orWhereHas('approvals', function ($query) use ($chiefTicketManagerRoleId, $user) {
                        $query->where('role_id', $chiefTicketManagerRoleId)->where('admin_id', $user->id);
                    });
            });
        }

        $tickets = $query->paginate();
        return view('ticketing::admin.pages.tickets.index', compact('tickets'));
    }

    public function edit(Ticket $ticket)
    {
        $user = auth()->user();

        $approval = TicketApprovals::query()->firstOrCreate([
            'ticket_id' => $ticket->id,
            'admin_id'  => $user->id,
            'role_id'   => $user->roles()->first()->id,
        ], [
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);


        return view('ticketing::admin.pages.tickets.edit', compact('ticket', 'approval'));
    }

    /**
     * @throws \Throwable
     */
    public function update(UpdateApprovalTicketRequest $request, Ticket $ticket)
    {
        $res = $this->approvalLogic->update($ticket, $request->validated());
        return Responder::fromResult($res, __('the ticket updated'), __('something went wrong'), route('admin.tickets.edit', $ticket))->go();

    }

    /**
     * @throws \Throwable
     */
    public function destroy(Ticket $ticket)
    {
        $res = $this->logic->delete($ticket);
        return Responder::fromResult($res, __('the ticket deleted'), __('something went wrong'), route('admin.tickets.index'))->go();

    }
}
