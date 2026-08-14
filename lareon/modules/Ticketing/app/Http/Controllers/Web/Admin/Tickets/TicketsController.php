<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\NewTicketRequest;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Teksite\Authorize\Models\Role;
use Teksite\Handler\Facade\Responder;

class TicketsController extends Controller implements HasMiddleware
{

    public function __construct(public TicketLogic $logic) {}

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

        $chiefTicketManagerRoleId = Role::query()
                                        ->where('title', 'chief ticket manager')
                                        ->value('id');

        $ticketManagerRoleId = Role::query()
                                   ->where('title', 'ticket manager')
                                   ->value('id');

        $query = Ticket::query();

        if ($user->hasRole('ticket manager')) {

            $query
                // اصلاً approval ندارد
                ->where(function ($query) use (
                    $ticketManagerRoleId,
                    $chiefTicketManagerRoleId,
                    $user
                ) {

                    $query

                        // حالت 1:
                        // هیچ approvalای وجود ندارد
                        ->whereDoesntHave('approvals')

                        // حالت 2:
                        // approval دارد، ولی مربوط به خود ticket manager است
                        ->orWhere(function ($query) use (
                            $ticketManagerRoleId,
                            $chiefTicketManagerRoleId,
                            $user
                        ) {

                            $query

                                // approval مربوط به خودم وجود دارد
                                ->whereHas('approvals', function ($query) use (
                                    $ticketManagerRoleId,
                                    $user
                                ) {
                                    $query
                                        ->where('role_id', $ticketManagerRoleId)
                                        ->where('admin_id', $user->id);
                                })

                                // ولی chief هنوز approval نساخته
                                ->whereDoesntHave('approvals', function ($query) use (
                                    $chiefTicketManagerRoleId
                                ) {
                                    $query->where('role_id', $chiefTicketManagerRoleId);
                                });
                        });
                });

        } elseif ($user->hasRole('chief ticket manager')) {

            $query
                // حتماً ticket manager باید تیکت را APPROVE کرده باشد
                ->whereHas('approvals', function ($query) use ($ticketManagerRoleId) {
                    $query
                        ->where('role_id', $ticketManagerRoleId)
                        ->where('status', TicketStatusEnum::APPROVED->value);
                })

                // chief approval:
                // یا اصلاً وجود ندارد
                // یا مربوط به خود این کاربر است
                ->where(function ($query) use (
                    $chiefTicketManagerRoleId,
                    $user
                ) {

                    $query
                        ->whereDoesntHave('approvals', function ($query) use (
                            $chiefTicketManagerRoleId
                        ) {
                            $query->where('role_id', $chiefTicketManagerRoleId);
                        })

                        ->orWhereHas('approvals', function ($query) use (
                            $chiefTicketManagerRoleId,
                            $user
                        ) {
                            $query
                                ->where('role_id', $chiefTicketManagerRoleId)
                                ->where('admin_id', $user->id);
                        });
                });
        }

        $tickets = $query->paginate();
        return view('ticketing::admin.pages.tickets.index', compact('tickets'));
    }

    public
    function edit(Ticket $ticket)
    {
        $user = auth()->user();

        $approvals = TicketApprovals::query()->firstOrCreate([
            'ticket_id' => $ticket->id,
            'admin_id'  => $user->id,
            'role_id'   => $user->roles()->first()->id,
        ], [
            'status' => TicketStatusEnum::IN_REVIEW->value,
        ]);
        dd($approvals);


        return view('ticketing::admin.pages.tickets.edit', compact('ticket', 'approvals'));
    }

    /**
     * @throws \Throwable
     */
    public
    function update(NewTicketRequest $request, Ticket $ticket) {}

    public
    function destroy(Ticket $ticket)
    {
        $res = $this->logic->delete($ticket);
        return Responder::fromResult($res, __('the ticket deleted'), __('something went wrong'), route('admin.tickets.index'))->go();

    }
}
