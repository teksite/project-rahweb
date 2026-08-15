<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\Log;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Logics\ApprovalTicketLogic;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Models\Ticket;

class TicketLogsController extends Controller implements HasMiddleware
{

    public function __construct(public TicketLogic $logic, public ApprovalTicketLogic $approvalLogic) {}

    public static function middleware()
    {
        return [
            new Middleware('can:admin.ticket.read'),
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


}
