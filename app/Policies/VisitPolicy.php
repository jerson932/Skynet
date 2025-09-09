<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor() || $user->isTecnico();
    }

    public function view(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isSupervisor()) {
            return $visit->supervisor_id === $user->id
                || optional($visit->tecnico)->supervisor_id === $user->id;
        }

        if ($user->isTecnico()) {
            return $visit->tecnico_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function update(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isSupervisor()) {
            return $visit->supervisor_id === $user->id
                || optional($visit->tecnico)->supervisor_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->isAdmin();
    }

    public function mark(User $user, Visit $visit): bool
    {
        return $user->id === $visit->tecnico_id;
    }
}
