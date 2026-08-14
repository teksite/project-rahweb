<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Teksite\Authorize\Models\Permission;
use Teksite\Authorize\Models\Role;

class BasicRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $allPermissions = Permission::query()->select(['id', 'title'])->get();
        $allPermissionIds = $allPermissions->pluck('id')->all();
        $userPermissionIds = $allPermissions->filter(fn($permission) => str_starts_with($permission->title, 'panel'))->pluck('id')->all();
        $ticketPermissionIds = $allPermissions->filter(fn($permission) => str_starts_with($permission->title, 'admin.ticket'))->pluck('id')->all();
        $ticketPermissionIds[] = $allPermissions->where('title', 'admin')->first()->id;

        foreach (Role::query()->whereIn('title', ['owner', 'administrator', 'admin',])->get() as $role) {
            $role->permissions()->sync($allPermissionIds);
        }

        foreach (Role::query()->whereIn('title', ['user'])->get() as $role) {
            $role->permissions()->sync($userPermissionIds);
        }



        foreach (Role::query()->whereIn('title', ['chief ticket manager', 'ticket manager'])->get() as $role) {
            $role->permissions()->sync($ticketPermissionIds);
        }
    }
}
