<?php

namespace Lareon\Modules\Auth\App\Providers;

use Lareon\Steward\App\Contracts\MenuRegisteringContract;
use Lareon\Steward\App\Enums\MenuAreaType;
use Lareon\Steward\App\Events\MenuRegisteringEvent;
use Lareon\Steward\App\Traits\HasMenu;

class MenuProvider implements MenuRegisteringContract
{

    use HasMenu;

    public function priority(): int
    {
        return 100;
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
                'title'  => 'accessibility',
                'order'  => 100,
                'icon'   => 'lock-closed',
                'active' => request()->routeIs('admin.authorize.*'),
            ],'auth'
        )->addManyItem(
        [
            [
                'title'  => 'roles',
                'order'  => 1,
                'route'  => 'admin.authorize.roles.index',
                'permission' => 'admin.role.read',
                'active' => request()->routeIs('admin.authorize.roles.*'),
            ],
            [
                'title'  => 'permissions',
                'order'  => 2,
                'route'  => 'admin.authorize.permissions.index',
                'permission' => 'admin.permission.read',
                'active' => request()->routeIs('admin.authorize.permissions.*'),
            ],
        ], 'auth');
    }

    protected function panel(MenuRegisteringEvent $event): void
    {

    }


}
