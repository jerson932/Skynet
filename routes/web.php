<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebVisitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebClientController;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// Ruta de health check simple para Railway
Route::get('/health', function () {
    return response('OK', 200)
        ->header('Content-Type', 'text/plain')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
});

// Ruta de diagnóstico completa
Route::get('/status', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'users_count' => \App\Models\User::count(),
            'csrf_token' => csrf_token(),
            'session_id' => session()->getId(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now(),
        ], 500);
    }
});

// Ruta de debug para login
Route::post('/debug-login', function (\Illuminate\Http\Request $request) {
    \Log::info('Debug login received', [
        'all_data' => $request->all(),
        'method' => $request->method(),
        'csrf' => $request->header('X-CSRF-TOKEN') ?: 'none',
        'session_token' => session()->token(),
    ]);
    
    return response()->json([
        'received' => $request->all(),
        'method' => $request->method(),
        'csrf_valid' => $request->session()->token() === $request->input('_token'),
    ]);
});

// (Opcional) que el dashboard lleve a la lista de visitas
Route::get('/dashboard', function () {
    return redirect()->route('visits.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', \App\Http\Middleware\EnsurePasswordChanged::class])->group(function () {
    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 👇 Visitas (HTML con Blade)
    Route::get('/visits', [WebVisitController::class, 'index'])->name('visits.index');
    Route::post('/visits/{visit}/check-in',  [WebVisitController::class, 'checkIn'])->name('visits.checkin');
    Route::post('/visits/{visit}/check-out', [WebVisitController::class, 'checkOut'])->name('visits.checkout');

    // 👇 necesarias para el botón
    Route::get('/visits/create', [WebVisitController::class, 'create'])->name('visits.create');
    Route::post('/visits',        [WebVisitController::class, 'store'])->name('visits.store');

    // Exports
    Route::get('/visits/export', [WebVisitController::class, 'export'])->name('visits.export');

    Route::get('/visits/{visit}/edit',  [WebVisitController::class, 'edit'])->name('visits.edit');
Route::put('/visits/{visit}',       [WebVisitController::class, 'update'])->name('visits.update');
Route::delete('/visits/{visit}',    [WebVisitController::class, 'destroy'])->name('visits.destroy');



     Route::resource('clients-web', WebClientController::class)
        ->parameters(['clients-web' => 'client'])
        ->names('clients.web'); // clients.web.index, .create, .store, etc

        Route::get('/visits/{visit}', [\App\Http\Controllers\WebVisitController::class, 'show'])
    ->name('visits.show');


Route::post('/visits/{visit}/send-mail', [\App\Http\Controllers\WebVisitController::class, 'sendMail'])
    ->name('visits.sendmail');    });

// Password change routes (accessible while must_change_password is true)
Route::get('/password/change', [\App\Http\Controllers\PasswordController::class, 'showChangeForm'])->name('password.change')->middleware('auth');
Route::post('/password/change', [\App\Http\Controllers\PasswordController::class, 'update'])->name('password.change.update')->middleware('auth');

// Settings (admin / supervisor)
Route::middleware('auth')->group(function () {
    Route::get('/settings', [\App\Http\Controllers\WebSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/create', [\App\Http\Controllers\WebSettingsController::class, 'create'])->name('settings.create');
    Route::post('/settings', [\App\Http\Controllers\WebSettingsController::class, 'store'])->name('settings.store');
    Route::post('/settings/transfer', [\App\Http\Controllers\WebSettingsController::class, 'transfer'])->name('settings.transfer');
    Route::post('/settings/{user}/reset-password', [\App\Http\Controllers\WebSettingsController::class, 'resetPassword'])->name('settings.reset_password');
    Route::get('/settings/{user}/edit', [\App\Http\Controllers\WebSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/{user}', [\App\Http\Controllers\WebSettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{user}', [\App\Http\Controllers\WebSettingsController::class, 'destroy'])->name('settings.destroy');
    Route::get('/settings/{user}/assign', [\App\Http\Controllers\WebSettingsController::class, 'assignForm'])->name('settings.assign');
    Route::post('/settings/{user}/assign', [\App\Http\Controllers\WebSettingsController::class, 'assignStore'])->name('settings.assign.store');
    Route::get('/settings/tecnico/{user}', [\App\Http\Controllers\WebSettingsController::class, 'showTecnico'])->name('settings.tecnico');
});

// Debug route to test mail sending (authenticated only)
// Safe check mode: append ?check=1 to see mail-related env/config without sending mail.
Route::middleware('auth')->get('/debug/mail-test', function () {
    try {
        $user = request()->user();

        // If admin requests a dry-run check, return useful environment/config values.
        if (request()->query('check')) {
            $env = [
                'app_env' => env('APP_ENV'),
                'mail_mailer' => env('MAIL_MAILER'),
                'postmark_token_exists' => !empty(env('POSTMARK_TOKEN')),
                'mail_from_address_env' => env('MAIL_FROM_ADDRESS'),
                'mail_from_name_env' => env('MAIL_FROM_NAME'),
                'mail_fallback_from_address' => env('MAIL_FALLBACK_FROM_ADDRESS'),
                'mail_reply_to_address' => env('MAIL_REPLY_TO_ADDRESS'),
                'config_mail_from_address' => config('mail.from.address'),
                'config_mail_from_name' => config('mail.from.name'),
            ];

            // Also include a small sample of the Visit model if an id is provided
            $visitSample = null;
            if (request()->filled('visit_id')) {
                $vid = intval(request()->query('visit_id'));
                $visitSample = \App\Models\Visit::with(['client','supervisor','tecnico'])->find($vid);
            }

            return response()->json([
                'status' => 'ok',
                'env' => $env,
                'visit_sample' => $visitSample,
                'timestamp' => now(),
            ]);
        }

        // Default behavior: attempt to send a simple test mail to the configured from address or the admin's email.
        $to = config('mail.from.address') ?: ($user?->email ?? '');
        \Illuminate\Support\Facades\Mail::raw('Prueba de envío desde Skynet: ' . now(), function($m) use ($to) {
            $m->to($to)->subject('Skynet - Test Mail');
        });
        return response()->json(['status' => 'sent', 'to' => $to]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Mail test failed', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
})->name('debug.mail')->middleware('auth');

// Simple authenticated JSON endpoint for user search (autocomplete)
Route::middleware('auth')->get('/_search/users', [\App\Http\Controllers\WebSettingsController::class, 'searchUsers'])->name('search.users');

require __DIR__.'/auth.php';
