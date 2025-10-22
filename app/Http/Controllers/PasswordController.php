<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('status','Contraseña actualizada');
    }
}
