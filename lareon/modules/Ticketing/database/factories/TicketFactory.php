<?php

namespace Lareon\Modules\Ticketing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lareon\Modules\User\App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Lareon\Modules\Ticketing\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'title'=>fake()->words(4, true),
            'body'=>fake()->paragraphs(4, true),
            'file'=>'/fake/file.png',
        ];
    }
}
