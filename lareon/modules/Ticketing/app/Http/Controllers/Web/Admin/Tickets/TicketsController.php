<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\NewTicketRequest;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApprovals;
use Teksite\Handler\Facade\Responder;

class TicketsController extends Controller implements HasMiddleware
{

    public function __construct(public TicketLogic $logic) {}

    public static function middleware()
    {
        return [
            new Middleware('can:admin.ticket.read'),
            new Middleware('can:admin.ticket.create', only: ['create', 'store']),
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

        $tickets = Ticket::query()
                         ->where(function ($query) use ($user) {

                             $query->whereDoesntHave('approvals')
                                 ->orWhereHas('approvals', function ($query) use ($user) {
                                     $query->where('admin_id', $user->id)->whereIn('role_id', $user->getDirectRoles(true));
                                 });
                         })
                         ->paginate();
        return view('ticketing::admin.pages.tickets.index', compact('tickets'));
    }

    public function edit(Ticket $ticket)
    {
        $user = auth()->user();

        $approvals=TicketApprovals::query()->firstOrNew([
            'ticket_id'=>$ticket->id,
            'admin_id'=>$user->id,
            'role_id'=>$user->roles()->first()->id,
        ]);


        return view('ticketing::admin.pages.tickets.edit', compact('ticket', 'approvals'));
    }

    /**
     * @throws \Throwable
     */
    public function update(NewTicketRequest $request, Ticket $ticket)
    {
        $res = $this->logic->create($request->validated());

        return Responder::fromResult($res, __('your ticket created'), __('something went wrong'), route('panel.tickets.index'))->go();
    }

    public function destroy(Ticket $ticket)
    {
        $res = $this->logic->delete($ticket);
        return Responder::fromResult($res, __('the ticket deleted'), __('something went wrong'), route('admin.tickets.index'))->go();

    }
}
