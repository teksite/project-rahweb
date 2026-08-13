<?php

namespace Lareon\Modules\Ticketing\App\Providers;

use Lareon\Steward\App\Contracts\MenuRegisteringContract;
use Lareon\Steward\App\Enums\MenuAreaType;
use Lareon\Steward\App\Events\MenuRegisteringEvent;
use Lareon\Steward\App\Traits\HasMenu;

class MenuProvider implements MenuRegisteringContract
{

    use HasMenu;

    public function priority(): int
    {
        return 102;
    }

    public function areas(): array
    {
        return [MenuAreaType::ADMIN, MenuAreaType::PANEL];
    }

    public function register(MenuRegisteringEvent $event): void
    {
        match ($event->area) {
            MenuAreaType::ADMIN => $this->admin($event),
            MenuAreaType::PANEL => $this->panel($event),
        };
    }

    protected function admin(MenuRegisteringEvent $event): void
    {
        $event->add(
            [
                'title'  => trans('tickets'),
                'order'  => 104,
                'icon'   => 'archive',
                'active' => request()->routeIs('admin.tickets.*'),
            ], 'user')
              ->addManyItem([
                  [
                      'title'      => trans('lareon::global.crud.titles.all', ['attribute' => trans('tickets')]),
                      'order'      => 1,
                      'route'      => 'admin.tickets.index',
                      'active'     => request()->routeIs('admin.tickets.index'),
                      'permission' => 'admin.ticket.read',

                  ],
              ], 'ticket');
    }

    protected function panel(MenuRegisteringEvent $event): void
    {
        $event->add([
            'title'  => 'tickets',
            'route'  => 'panel.tickets.index',
            'icon'   => 'archive',
            'order'  => 2,
            'active' => request()->routeIs('panel.tickets.*'),
        ], 'tickets');

    }


}
