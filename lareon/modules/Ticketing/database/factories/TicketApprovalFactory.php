<?php

namespace Lareon\Modules\Ticketing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\User\App\Models\User;
use Teksite\Authorize\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Lareon\Modules\Ticketing\App\Models\Ticket>
 */
class TicketApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
             return [
                 'ticket_id' => Ticket::factory(),
                 'admin_id' => User::factory(),
                 'role_id' => Role::query()->first()?->id,
                 'round' => 1,
                 'status' => 0,
                 'review' => null,
             ];
    }
}
