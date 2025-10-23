<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
        // Verificar autenticación explícitamente
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para continuar');
        }

        try {
            $this->authorize('mark', $visit);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', 'No tiene permisos para realizar check-in en esta visita');
        }

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $visit->update([
            'check_in_at'  => now(),
            'check_in_lat' => $data['lat'],
            'check_in_lng' => $data['lng'],
        ]);

        return back()->with('status', 'Check-in registrado exitosamente');
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
        return back()->with('error', 'El cliente no tiene email configurado.');
    }

    try {
        // Cargar relaciones para el PDF/mail
        $visit->load(['client','supervisor','tecnico']);

        // Verificar configuración de correo
        if (!config('mail.mailers.smtp.host')) {
            throw new \Exception('Configuración de correo no disponible');
        }

        \Illuminate\Support\Facades\Mail::to($visit->client->email)
            ->send(new \App\Mail\VisitClosedMail($visit));

        \Log::info('Email enviado exitosamente', [
            'visit_id' => $visit->id,
            'client_email' => $visit->client->email,
            'user_id' => $user->id
        ]);

        return back()->with('status', 'Correo enviado exitosamente a ' . $visit->client->email);

    } catch (\Exception $e) {
        // Log full error for debugging
        \Log::error('Error enviando correo', [
            'visit_id' => $visit->id,
            'client_email' => $visit->client->email,
            'error' => $e->getMessage(),
            'user_id' => $user->id
        ]);

        // Friendly, actionable message for common network/SMTP errors
        $raw = $e->getMessage();
        $friendly = 'Error al enviar correo.';

        if (str_contains(strtolower($raw),'connection') || str_contains(strtolower($raw),'stream_socket_client') || str_contains(strtolower($raw),'timed out')) {
            $friendly = 'No se pudo conectar con el servidor SMTP. En Railway muchos proveedores bloquean el puerto 587 o requieren un proveedor transaccional (SendGrid, Mailgun). Revisa las variables de entorno MAIL_* en tu proyecto (por ejemplo usa SendGrid con MAIL_HOST=smtp.sendgrid.net, MAIL_USERNAME=apikey y MAIL_PASSWORD=\"TU_API_KEY\").';
        } elseif (str_contains(strtolower($raw),'authentication') || str_contains(strtolower($raw),'535')) {
            $friendly = 'Fallo de autenticación SMTP. Revisa MAIL_USERNAME y MAIL_PASSWORD en las variables de entorno.';
        } else {
            // keep a concise version of the raw message for admins
            $friendly = 'Error al enviar correo: ' . (strlen($raw) > 250 ? substr($raw,0,247).'...' : $raw);
        }

        return back()->with('error', $friendly);
    }
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

        // En Railway, siempre usar CSV para evitar problemas
        if ($format === 'xlsx' || str_contains($accept, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            $filename = 'visits_report_'.now()->format('Ymd_His').'.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];
            // Continuar con el procesamiento CSV normal
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
