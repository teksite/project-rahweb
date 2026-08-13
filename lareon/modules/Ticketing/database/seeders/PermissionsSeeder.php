<?php

namespace Lareon\Modules\Ticketing\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Teksite\Authorize\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::query()->insert([

            /*ADMIN*/
            [
                'title'       => 'admin.ticket.read',
                'description' => 'have access to read one or all tickets (in the admin panel)',
            ],
            [
                'title'       => 'admin.ticket.create',
                'description' => 'have access to create a new ticket (in the admin panel)',
            ],
            [
                'title'       => 'admin.ticket.edit',
                'description' => 'have access to edit tickets (in the admin panel)',
            ],
            [
                'title'       => 'admin.ticket.delete',
                'description' => 'have access to delete tickets (in the admin panel)',
            ],


            /*PANEl*/
            [
                'title'       => 'panel.ticket.read',
                'description' => 'have access to read one or all tickets (in the panel panel)',
            ],
            [
                'title'       => 'panel.ticket.create',
                'description' => 'have access to create a new ticket (in the panel panel)',
            ],
            [
                'title'       => 'panel.ticket.edit',
                'description' => 'have access to edit tickets (in the panel panel)',
            ],
            [
                'title'       => 'panel.ticket.delete',
                'description' => 'have access to delete tickets (in the panel panel)',
            ],

        ]);
    }
}
