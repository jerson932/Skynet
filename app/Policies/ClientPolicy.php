<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    // Ver listado
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor() || $user->isTecnico();
    }

    // Ver 1 cliente
    public function view(User $user, Client $client): bool
    {
        return $this->viewAny($user);
    }

    // Crear
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    // Actualizar
    public function update(User $user, Client $client): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    // Borrar
    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin(); // SOLO admin
    }
}
