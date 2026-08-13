<?php

namespace Lareon\Modules\Ticketing\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Teksite\Authorize\Models\Permission;
use Teksite\Authorize\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->insert([
            [
                'title'       => 'chief ticket manager ',
                'hierarchy'   => '10',
                'description' => 'second ticket manger',
            ],
            [
                'title'       => 'ticket manager 2',
                'hierarchy'   => '11',
                'description' => 'first ticket manger',
            ],
        ]);

    }
}
