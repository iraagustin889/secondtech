<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->buyer_id || $user->id === $conversation->seller_id;
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
