<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    // Ver listado
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor() || $user->isTecnico();
    }

    // Ver una visita
    public function view(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isSupervisor()) return $visit->supervisor_id === $user->id || $visit->tecnico_id === $user->id;
        if ($user->isTecnico())    return $visit->tecnico_id === $user->id;
        return false;
    }

    // Crear (solo supervisor o admin)
   public function create(User $user): bool
{
    return $user->isAdmin() || $user->isSupervisor();
}


    // Actualizar (admin o supervisor dueño de la visita)
    public function update(User $user, Visit $visit): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isSupervisor()) return $visit->supervisor_id === $user->id;
        return false;
    }

    // Borrar (solo admin)
    public function delete(User $user, Visit $visit): bool
    {
        return $user->isAdmin();
    }

    // Marcar check-in/out (solo el técnico asignado)
   public function mark(User $user, Visit $visit): bool
    {
        return $user->id === $visit->tecnico_id;
    }
}
