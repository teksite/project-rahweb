<?php

namespace Lareon\Modules\Ticketing\App\Policy;

use Lareon\Modules\Ticketing\App\Models\Ticket;
use Lareon\Modules\Ticketing\App\Models\TicketApproval;
use \Lareon\Modules\User\App\Models\User;

class TicketApprovalPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    public function create(User $user , TicketApproval $approval): bool
    {
        return $user->id === $post->user_id;

    }

}
