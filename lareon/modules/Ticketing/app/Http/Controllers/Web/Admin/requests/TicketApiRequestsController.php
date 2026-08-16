<?php

namespace Lareon\Modules\Ticketing\App\Http\Controllers\Web\Admin\requests;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Lareon\Modules\Ticketing\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lareon\Modules\Ticketing\App\Logics\RequestsLogic;
use Lareon\Modules\Ticketing\App\Models\TicketApi;

class TicketApiRequestsController extends Controller implements HasMiddleware
{

    public function __construct(public RequestsLogic $logic) {}

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
        $items = $this->logic->all()->result;
        return view('ticketing::admin.pages.requests.index', compact('items'));
    }


    public function show(TicketApi $item)
    {
        return view('ticketing::admin.pages.requests.show', compact('item'));
    }


}
