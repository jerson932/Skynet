<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route; // 👈 importar Route
use App\Http\Controllers\ClientController;
use App\Models\User;

Route::get('/ping', fn () => ['pong' => true]); // 👈 ruta de prueba

Route::post('/login', function (Request $req) {
    $req->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $req->email)->first();

    if (!$user || !Hash::check($req->password, $user->password)) { // 👈 corrección
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $token = $user->createToken('api')->plainTextToken;
    return ['token' => $token, 'role' => $user->role_id];
});

Route::middleware('auth:sanctum')->get('/me', function (Request $req) {
    return $req->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
});
