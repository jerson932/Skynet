<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisitClosedMail;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct()
    {
        // Aplica VisitPolicy automáticamente a los métodos REST
        $this->authorizeResource(Visit::class, 'visit');
    }

    // GET /api/visits
    // Admin ve todo. Supervisor ve sus visitas y las de sus técnicos.
    // Técnico ve solo las asignadas a él.
 public function index(Request $request)
{
    $user = $request->user();

    $q = \App\Models\Visit::with(['client','supervisor','tecnico'])
        ->orderBy('scheduled_at','desc');

    if ($user->isAdmin()) {
        // todo
    } elseif ($user->isSupervisor()) {
        $q->where(function ($w) use ($user) {
            $w->where('supervisor_id', $user->id)
              ->orWhereHas('tecnico', function($t) use ($user) {
                  $t->where('supervisor_id', $user->id);
              });
        });
    } else { // técnico
        $q->where('tecnico_id', $user->id);
    }

    if ($date = $request->query('date')) {
        $q->whereDate('scheduled_at', $date);
    }

    return response()->json($q->paginate(05));
}


    // POST /api/visits (supervisor o admin)
   public function store(Request $request)
{
    $data = $request->validate([
        'client_id'    => 'required|exists:clients,id',
        'tecnico_id'   => 'required|exists:users,id',
        'scheduled_at' => 'required|date',
        'notes'        => 'nullable|string',
    ]);

    $current = $request->user();

    if ($current->isSupervisor()) {
        // Si la crea un supervisor, él queda como supervisor de la visita
        $data['supervisor_id'] = $current->id;
    } else {
        // Si la crea Admin, usamos el supervisor del técnico
        $tec = \App\Models\User::find($data['tecnico_id']);
        $data['supervisor_id'] = $tec?->supervisor_id ?? $current->id; // fallback a admin si no tuviera
    }

    $visit = \App\Models\Visit::create($data);

    return response()->json($visit->load(['client','supervisor','tecnico']), 201);
}



    // GET /api/visits/{visit}
    public function show(Visit $visit)
    {
        return response()->json($visit->load(['client','supervisor','tecnico']));
    }

    // PUT /api/visits/{visit} (admin o supervisor dueño)
   public function update(Request $request, Visit $visit)
{
    $data = $request->validate([
        'client_id'     => 'sometimes|exists:clients,id',
        'tecnico_id'    => 'sometimes|exists:users,id',
        'scheduled_at'  => 'sometimes|date',
        'notes'         => 'nullable|string',
    ]);

    // 👇 si cambiaron el técnico, alinear supervisor_id
    if (isset($data['tecnico_id'])) {
        $tecnico = User::findOrFail($data['tecnico_id']);
        $data['supervisor_id'] = $tecnico->supervisor_id ?? $visit->supervisor_id;
    }

    $visit->update($data);

    return response()->json($visit->load(['client','supervisor','tecnico']));
}
    // DELETE /api/visits/{visit} (solo admin)
    public function destroy(Visit $visit)
    {
        $visit->delete();
        return response()->json(['deleted' => true]);
    }

    // POST /api/visits/{visit}/check-in  (solo técnico asignado)
    public function checkIn(Request $request, Visit $visit)
    {
        $this->authorize('mark', $visit); // usa método mark() de la policy

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $visit->update([
            'check_in_at'  => now(),
            'check_in_lat' => $data['lat'],
            'check_in_lng' => $data['lng'],
        ]);

        return response()->json($visit->fresh()->load(['client','supervisor','tecnico']));
    }

    // POST /api/visits/{visit}/check-out (solo técnico asignado)
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

    // Recargar relaciones
    $visit->load(['client','supervisor','tecnico']);

    // 👉 Enviar correo si el cliente tiene email
    if (!empty($visit->client->email)) {
        \Illuminate\Support\Facades\Mail::to($visit->client->email)
            ->send(new \App\Mail\VisitClosedMail($visit));
    }

    return response()->json($visit);
}
}
