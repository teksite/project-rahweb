<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Events\UpdateTicketStatusEvent;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\UpdateApprovalTicketRequest;
use Lareon\Modules\Ticketing\App\Logics\ApprovalTicketLogic;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
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
        $tickets = $this->logic->all()->result;
        return view('ticketing::admin.pages.tickets.index', compact('tickets'));
    }


    public function show(Ticket $ticket)
    {
        return view('ticketing::admin.pages.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $approval = $this->approvalLogic->firstOrCreate($ticket)->result;
        return view('ticketing::admin.pages.tickets.edit', compact('ticket', 'approval'));
    }

    /**
     * @throws \Throwable
     */
    public function update(UpdateApprovalTicketRequest $request, Ticket $ticket)
    {
        $res = $this->approvalLogic->update($ticket, $request->validated());
        if ($res->success) event(new UpdateTicketStatusEvent($ticket, $res->result));
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
