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
        ->header('Content-Type', 'text/plain');
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
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now(),
        ], 500);
    }
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

// Simple authenticated JSON endpoint for user search (autocomplete)
Route::middleware('auth')->get('/_search/users', [\App\Http\Controllers\WebSettingsController::class, 'searchUsers'])->name('search.users');

require __DIR__.'/auth.php';
