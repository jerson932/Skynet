<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class WebVisitController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();
    $q = Visit::with(['client','supervisor','tecnico'])
              ->orderBy('scheduled_at','desc');

    if ($user->isAdmin()) {
        // ve todas
    } elseif ($user->isSupervisor()) {
        $q->where(function($w) use ($user){
            $w->where('supervisor_id', $user->id)
              ->orWhere('tecnico_id', $user->id);
        });
    } else { // técnico
        $q->where('tecnico_id', $user->id);
    }

    // ✅ Filtro por fecha SOLO si viene ?date=YYYY-MM-DD
    if ($date = $request->query('date')) {
        $q->whereDate('scheduled_at', $date);
    }

    $visits = $q->paginate(10);

    // Pasamos la fecha (si existe) solo para que la vista sepa qué mostrar en el input
    return view('visits.index', [
        'visits' => $visits,
        'today'  => $request->query('date') // puede ser null
    ]);
}

    // Check-in (solo técnico asignado)
    public function checkIn(Request $request, Visit $visit)
    {
        $this->authorize('mark', $visit);

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $visit->update([
            'check_in_at'  => now(),
            'check_in_lat' => $data['lat'],
            'check_in_lng' => $data['lng'],
        ]);

        return back()->with('status', 'Check-in registrado');
    }

    // Check-out (solo técnico asignado)
    public function checkOut(Request $request, Visit $visit)
    {
        $this->authorize('mark', $visit);

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $visit->update([
            'check_out_at'  => now(),
            'check_out_lat' => $data['lat'],
            'check_out_lng' => $data['lng'],
        ]);

        return back()->with('status', 'Check-out registrado');
    }

    // Formulario de creación (Admin/Supervisor)
    public function create(Request $request)
    {
        $user = $request->user();
        if (!($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'No autorizado');
        }

        $clients  = Client::orderBy('name')->get();
        $tecnicos = User::whereHas('role', fn($q)=>$q->where('slug','tecnico'))
                        ->orderBy('name')->get();

        return view('visits.create', compact('clients','tecnicos'));
    }

    // Guardar nueva visita
    public function store(Request $request)
    {
        $user = $request->user();
        if (!($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'No autorizado');
        }

        $data = $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'tecnico_id'   => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $data['supervisor_id'] = $user->id;

        Visit::create($data);

        return redirect()->route('visits.index')
                         ->with('status','Visita creada correctamente.');
    }
}
