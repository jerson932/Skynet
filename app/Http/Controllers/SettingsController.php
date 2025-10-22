<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/settings/users
    // Admin: lista todos los usuarios (opcional filter por role)
    // Supervisor: lista solo sus técnicos
    public function indexUsers(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $q = User::with('role');
            if ($role = $request->query('role')) {
                $q->whereHas('role', fn($r) => $r->where('slug', $role));
            }
            return response()->json($q->paginate(20));
        }

        if ($user->isSupervisor()) {
            return response()->json($user->tecnicos()->with('role')->get());
        }

        abort(403);
    }

    // POST /api/settings/users
    // Admin only: crear supervisor o tecnico
    public function storeUser(Request $request)
    {
        $user = $request->user();
        if (! $user->isAdmin()) abort(403);

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:supervisor,tecnico',
            'password' => 'nullable|string|min:6',
            'supervisor_id' => 'nullable|exists:users,id'
        ]);

        $role = Role::where('slug', $data['role'])->firstOrFail();

        $password = $data['password'] ?? '#1company';

        $u = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role_id' => $role->id,
            'supervisor_id' => $data['supervisor_id'] ?? null,
            'must_change_password' => empty($data['password']),
        ]);

        return response()->json($u->load('role'), 201);
    }

    // POST /api/settings/supervisors/{supervisor}/tecnicos
    // Admin only: asigna varios técnicos a un supervisor
    public function assignTecnicos(Request $request, User $supervisor)
    {
        $user = $request->user();
        if (! $user->isAdmin()) abort(403);

        $request->validate([
            'tecnico_ids' => 'required|array',
            'tecnico_ids.*' => 'exists:users,id'
        ]);

        DB::table('users')->whereIn('id', $request->input('tecnico_ids'))
            ->update(['supervisor_id' => $supervisor->id]);

        return response()->json(['assigned' => true]);
    }
}
