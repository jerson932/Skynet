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
    $date = $request->query('date'); // puede ser null
    
$q = \App\Models\Visit::with(['client','supervisor','tecnico'])
    ->orderBy('scheduled_at','desc')
    ->orderBy('created_at','desc');

    // Filtro por rol
    $q->when($user->isAdmin(), function ($q) {
        // Admin ve todo (no aplica filtro)
    })->when($user->isSupervisor(), function ($q) use ($user) {
        $q->where(function ($w) use ($user) {
            $w->where('supervisor_id', $user->id)
              ->orWhereHas('tecnico', function ($t) use ($user) {
                  $t->where('supervisor_id', $user->id);
              });
        });
    })->when($user->isTecnico(), function ($q) use ($user) {
        $q->where('tecnico_id', $user->id);
    });

    // Filtro por fecha (opcional)
    $q->when($date, fn($q) => $q->whereDate('scheduled_at', $date));

    $visits = $q->paginate(05);

    return view('visits.index', [
        'visits' => $visits,
        'date'   => $date,
        'today'  => $date ?? now()->toDateString(),
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
        if (!($user->isAdmin() || $user->isSupervisor() || $user->isTecnico())) {
            abort(403, 'No autorizado');
        }

        $clients  = Client::orderBy('name')->get();
        // Si es supervisor, mostrar solo sus técnicos; si es admin, mostrar todos
        if ($user->isSupervisor()) {
            $tecnicos = $user->tecnicos()->orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $tecnicos = User::whereHas('role', fn($q)=>$q->where('slug','tecnico'))->orderBy('name')->get();
        } else { // tecnico -> only self
            $tecnicos = collect([$user]);
        }

        return view('visits.create', compact('clients','tecnicos'));
    }

    // Guardar nueva visita
   public function store(Request $request)
{
    $user = $request->user();
    // Admin, supervisor, or tecnico can create (tecnico only for themselves)
    if (!($user->isAdmin() || $user->isSupervisor() || $user->isTecnico())) {
        abort(403, 'No autorizado');
    }


    $data = $request->validate([
        'client_id'    => 'required|exists:clients,id',
        'tecnico_id'   => 'required|exists:users,id',
        'scheduled_at' => 'required|date',
        'notes'        => 'nullable|string',
    ]);

    // If tecnico is creating, ensure they set tecnico_id to themselves
    if ($user->isTecnico() && $data['tecnico_id'] != $user->id) {
        abort(403, 'No autorizado para asignar a otro técnico');
    }

    // supervisor = supervisor del técnico (si existe); si no, el creador
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

    // Simple CSV export for reports (admin / supervisor / tecnico)
    public function export(Request $request)
    {
        $user = $request->user();

        $q = \App\Models\Visit::with(['client','supervisor','tecnico'])->orderBy('scheduled_at','desc');

        if ($user->isSupervisor()) {
            $q->where(function($w) use ($user){
                $w->where('supervisor_id', $user->id)
                  ->orWhereHas('tecnico', fn($t)=> $t->where('supervisor_id', $user->id));
            });
        } elseif ($user->isTecnico()) {
            $q->where('tecnico_id', $user->id);
        }

        // optional filters
        if ($request->filled('from')) $q->whereDate('scheduled_at', '>=', $request->input('from'));
        if ($request->filled('to')) $q->whereDate('scheduled_at', '<=', $request->input('to'));

        $visits = $q->get();

        $filename = 'visits_report_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Determine requested format (accept both query param and regular input)
        $format = strtolower((string) $request->get('format', ''));
        $accept = strtolower((string) $request->header('Accept', ''));

        // If caller requested XLSX format (via ?format=xlsx or via Accept header), generate spreadsheet
        if ($format === 'xlsx' || str_contains($accept, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            $export = new \App\Exports\VisitsExport($visits);
            $filename = 'visits_report_'.now()->format('Ymd_His').'.xlsx';
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            $stream = $export->toXlsxStream();
            return response()->stream($stream, 200, $headers);
        }

        $columns = ['id','client','tecnico','supervisor','scheduled_at','check_in_at','check_out_at','status','notes'];

        $callback = function() use ($visits, $columns) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $columns);
            foreach ($visits as $v) {
                $status = $v->check_out_at ? 'completada' : ($v->check_in_at ? 'en curso' : 'pendiente');
                fputcsv($fh, [
                    $v->id,
                    $v->client->name ?? '',
                    optional($v->tecnico)->name ?? '',
                    optional($v->supervisor)->name ?? '',
                    $v->scheduled_at,
                    $v->check_in_at,
                    $v->check_out_at,
                    $status,
                    str_replace(["\r","\n"], [' ',' '], $v->notes ?? ''),
                ]);
            }
            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }
}
