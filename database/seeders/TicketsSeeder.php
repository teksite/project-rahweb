<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Lareon\Modules\Ticketing\App\Models\Ticket;

class TicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ticket::query()->insert([
            [
                'user_id' => 4,
                'title'   => 'ticket 1',
                'body'    => 'fake messages , :)',
                'file'    => '/assets/tickets/4/47wlK0F0ZKSkfEiWQphRWBCyKXeXnFgPDq8nFtQo.jpg',
            ],
            [
                'user_id' => 4,
                'title'   => 'ticket 2',
                'body'    => 'fake messages , :)',
                'file'    => '/assets/tickets/4/4jWS32T7J2L72dVeBNb36is4cG578DD1cChcu70r.jpg',
            ],
            [
                'user_id' => 4,
                'title'   => 'ticket 3',
                'body'    => 'fake messages , :)',
                'file'    => '/assets/tickets/4/UautEZTRhjBW7oI50X99R1NQKzfJeuix5W23sJLh.jpg',
            ],
        ]);
    }




}
