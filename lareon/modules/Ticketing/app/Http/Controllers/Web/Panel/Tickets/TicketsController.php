<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Panel\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;

class TicketsController extends Controller implements HasMiddleware
{

    public function __construct(public TicketLogic $logic) {}

    public static function middleware()
    {
        return [
            new Middleware('can:panel.ticket.read'),
            new Middleware('can:panel.ticket.create', only: ['create', 'store']),
            new Middleware('can:panel.ticket.edit', only: ['edit', 'update']),
            new Middleware('can:panel.ticket.delete', only: ['destroy']),
        ];
    }

    /**
     * @throws \Throwable
     */
    public function index()
    {
        $tickets = $this->logic->allByUser();
        return view('ticketing::panel.pages.tickets.index', compact('tickets'));
    }

    public function create()
    {
        
    }

    public function store()
    {
        
    }
}
