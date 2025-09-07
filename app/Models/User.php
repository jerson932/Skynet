<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function role() {             // relación con roles
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $slug): bool {
        return optional($this->role)->slug === $slug;
    }

    public function isAdmin(): bool      { return $this->hasRole('admin'); }
    public function isSupervisor(): bool { return $this->hasRole('supervisor'); }
    public function isTecnico(): bool    { return $this->hasRole('tecnico'); }
}
