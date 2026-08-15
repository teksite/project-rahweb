<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Panel\Tickets;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Events\NewTicketEvent;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Http\Requests\Panel\NewTicketRequest;
use Lareon\Modules\Ticketing\App\Logics\TicketLogic;
use Lareon\Modules\Ticketing\App\Services\UploadFileService;
use Teksite\Handler\Facade\Responder;

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
        $tickets = $this->logic->allByUser()->result;

        return view('ticketing::panel.pages.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('ticketing::panel.pages.tickets.create');
    }

    /**
     * @throws \Throwable
     */
    public function store(NewTicketRequest $request)
    {

        $userId = auth()->id();
        
        $file = (new UploadFileService())->store($request->file('file'), $userId);

        $inputs = array_merge($request->validated(), ['file' => $file, 'user_id' => $userId]);

        $res = $this->logic->create($inputs);

        if ($res->success) event(new NewTicketEvent($res->result));

        return Responder::fromResult($res, __('your ticket created'), __('something went wrong'), route('panel.tickets.index'))->go();
    }
}
