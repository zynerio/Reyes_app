<?php

namespace App\Policies;

use App\Models\GiftList;
use App\Models\ListShare;
use App\Models\User;

class GiftListPolicy
{
    protected function hasWrite(User $user, GiftList $list): bool
    {
        if ($list->owner_id === $user->id) {
            return true;
        }
        return ListShare::query()
            ->where('list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('permission', 'write')
            ->exists();
    }

    protected function hasRead(User $user, GiftList $list): bool
    {
        if ($this->hasWrite($user, $list)) {
            return true;
        }
        return ListShare::query()
            ->where('list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('permission', 'read')
            ->exists();
    }

    public function view(User $user, GiftList $list): bool
    {
        return $this->hasRead($user, $list);
    }

    public function update(User $user, GiftList $list): bool
    {
        return $this->hasWrite($user, $list);
    }

    public function delete(User $user, GiftList $list): bool
    {
        return $user->id === $list->owner_id;
    }

    public function share(User $user, GiftList $list): bool
    {
        return $user->id === $list->owner_id;
    }
}

