<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebVisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// (Opcional) que el dashboard lleve a la lista de visitas
Route::get('/dashboard', function () {
    return redirect()->route('visits.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
});

require __DIR__.'/auth.php';
