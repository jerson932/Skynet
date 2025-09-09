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

    // 👇 supervisor = supervisor del técnico (si existe); si no, el creador
    $tecnico = \App\Models\User::findOrFail($data['tecnico_id']);
    $data['supervisor_id'] = $tecnico->supervisor_id ?? $user->id;

    \App\Models\Visit::create($data);

    return redirect()->route('visits.index')
                     ->with('status','Visita creada correctamente.');
}

public function edit(Request $request, \App\Models\Visit $visit) {
    $this->authorize('update', $visit);
    $clients  = \App\Models\Client::orderBy('name')->get();
    $tecnicos = \App\Models\User::whereHas('role', fn($q)=>$q->where('slug','tecnico'))->orderBy('name')->get();
    return view('visits.edit', compact('visit','clients','tecnicos'));
}

public function update(Request $request, \App\Models\Visit $visit) {
    $this->authorize('update', $visit);
    $data = $request->validate([
        'client_id'    => 'required|exists:clients,id',
        'tecnico_id'   => 'required|exists:users,id',
        'scheduled_at' => 'required|date',
        'notes'        => 'nullable|string',
    ]);
    $visit->update($data);
    return redirect()->route('visits.index')->with('status','Visita actualizada');
}

public function destroy(Request $request, \App\Models\Visit $visit) {
    $this->authorize('delete', $visit);
    $visit->delete();
    return redirect()->route('visits.index')->with('status','Visita eliminada');


}

public function sendMail(Request $request, \App\Models\Visit $visit)
{
    $user = $request->user();

    // Permisos: Admin, o Supervisor dueño de la visita, o Técnico asignado
    $allowed =
        $user->isAdmin() ||
        ($user->isSupervisor() && ($visit->supervisor_id === $user->id || $visit->tecnico_id === $user->id)) ||
        ($user->isTecnico() && $visit->tecnico_id === $user->id);

    if (!$allowed) abort(403, 'No autorizado');

    if (empty($visit->client?->email)) {
        return back()->with('status', 'El cliente no tiene email configurado.');
    }

    // Cargar relaciones para el PDF/mail
    $visit->load(['client','supervisor','tecnico']);

    \Illuminate\Support\Facades\Mail::to($visit->client->email)
        ->send(new \App\Mail\VisitClosedMail($visit));

    return back()->with('status', 'Correo enviado a '.$visit->client->email);
}

public function show(Request $request, \App\Models\Visit $visit)
{
    // Usa la policy VisitPolicy@view (ya la tienes)
    $this->authorize('view', $visit);

    // Carga relaciones necesarias
    $visit->load(['client','supervisor','tecnico']);

    return view('visits.show', compact('visit'));
}
}
