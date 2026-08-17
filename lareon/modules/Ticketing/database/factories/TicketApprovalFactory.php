<?php

namespace Lareon\Modules\Ticketing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum;
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
        $role = Role::query()->first();

        return [
                 'ticket_id' => Ticket::factory(),
                 'admin_id' => User::factory(),
                 'role_id' => $role?->id,
                 'round' => 1,
                 'status' => TicketStatusEnum::IN_REVIEW,
                 'review' => null,
             ];
    }
}
