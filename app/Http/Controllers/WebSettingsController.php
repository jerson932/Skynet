<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WebSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $users = User::with('role')->paginate(20);
            return view('settings.index', compact('users'));
        }

        if ($user->isSupervisor()) {
            $tecnicos = $user->tecnicos()->with('role')->paginate(20);
            return view('settings.supervisor', compact('tecnicos'));
        }

        if ($user->isTecnico()) {
            $supervisor = $user->supervisor;
            return view('settings.tecnico', compact('supervisor'));
        }

        abort(403);
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $roles = Role::whereIn('slug', ['supervisor','tecnico'])->get();
        $supervisors = User::whereHas('role', fn($q)=> $q->where('slug','supervisor'))->get();
        return view('settings.create', compact('roles','supervisors'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $roles = Role::whereIn('slug', ['supervisor','tecnico'])->get();
        $supervisors = User::whereHas('role', fn($q)=> $q->where('slug','supervisor'))->get();
        return view('settings.edit', compact('user','roles','supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:supervisor,tecnico',
            'supervisor_id' => 'nullable|exists:users,id'
        ]);

        $role = Role::where('slug', $data['role'])->firstOrFail();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $role->id;
        $user->supervisor_id = $data['supervisor_id'] ?? null;
        $user->save();

        return redirect()->route('settings.index')->with('status','Usuario actualizado');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        // prevent deleting oneself
        if ($request->user()->id === $user->id) {
            return redirect()->route('settings.index')->with('error','No puedes eliminar tu propio usuario');
        }

        // detach any users that reference this user as supervisor to avoid FK constraint errors
        // and clear visit references (tecnico_id / supervisor_id) that point to this user
        DB::transaction(function() use ($user) {
            DB::table('users')->where('supervisor_id', $user->id)->update(['supervisor_id' => null]);

            // null any visits where this user is assigned as tecnico or supervisor
            DB::table('visits')->where('tecnico_id', $user->id)->update(['tecnico_id' => null]);
            DB::table('visits')->where('supervisor_id', $user->id)->update(['supervisor_id' => null]);

            $user->delete();
        });
        return redirect()->route('settings.index')->with('status','Usuario eliminado');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

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
            // administrators should not be forced to change password on first login
            'must_change_password' => ($role->slug === 'admin') ? false : empty($data['password']),
        ]);

        return redirect()->route('settings.index')->with('status','Usuario creado');
    }

    // Transferir técnicos de un supervisor a otro
    public function transfer(Request $request)
    {
        $this->authorize('update', User::class);
        $data = $request->validate([
            'from_supervisor' => 'required|exists:users,id',
            'to_supervisor' => 'required|exists:users,id',
        ]);

        DB::table('users')->where('supervisor_id', $data['from_supervisor'])
            ->update(['supervisor_id' => $data['to_supervisor']]);

        return redirect()->route('settings.index')->with('status','Técnicos transferidos');
    }

    // Show form to assign multiple technicians to a supervisor
    public function assignForm(User $user)
    {
        $this->authorize('update', User::class);

        // ensure target is a supervisor
        if (!$user->isSupervisor()) abort(403);

        // fetch all technicians
        $techRole = Role::where('slug','tecnico')->first();
        // include supervisor relation to show current assignment
        $tecnicos = User::where('role_id', $techRole->id)->with('supervisor')->get();

        return view('settings.assign', compact('user','tecnicos'));
    }

    // Process assignment of selected technicians to the supervisor
    public function assignStore(Request $request, User $user)
    {
        $this->authorize('update', User::class);

        if (!$user->isSupervisor()) abort(403);


        $data = $request->validate([
            'tecnicos' => 'nullable|array',
            'tecnicos.*' => 'exists:users,id'
        ]);

        $selected = $data['tecnicos'] ?? [];

        // Ensure none of the selected technicians are already assigned to another supervisor
        $conflicts = DB::table('users')
            ->whereIn('id', $selected)
            ->whereNotNull('supervisor_id')
            ->where('supervisor_id', '<>', $user->id)
            ->pluck('id')
            ->toArray();

        if (!empty($conflicts)) {
            return redirect()->back()->with('error', 'Algunos técnicos ya están asignados a otro supervisor.');
        }

        // Clear supervisor for technicians that were previously assigned to this supervisor but not in the new list
        DB::table('users')->where('supervisor_id', $user->id)->whereNotIn('id', $selected)->update(['supervisor_id' => null]);

        // Assign selected technicians to this supervisor (only those unassigned or already assigned to this supervisor)
        DB::table('users')
            ->whereIn('id', $selected)
            ->where(function($q) use ($user) {
                $q->whereNull('supervisor_id')->orWhere('supervisor_id', $user->id);
            })
            ->update(['supervisor_id' => $user->id]);

        return redirect()->route('settings.index')->with('status','Técnicos asignados');
    }

    // Show a technician detail (used by supervisor->view or admin)
    public function showTecnico(User $user)
    {
        $this->authorize('view', $user);

        $supervisor = $user->supervisor;
        return view('settings.tecnico', compact('supervisor','user'));
    }

    // Admin: reset password of a user to default and force change
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('update', User::class);

        $user->password = Hash::make('#1company');
        // don't force admins to change password
        if (!method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            $user->must_change_password = true;
        }
        $user->save();

        return redirect()->route('settings.index')->with('status','Contraseña reseteada para '.$user->email);
    }
}
