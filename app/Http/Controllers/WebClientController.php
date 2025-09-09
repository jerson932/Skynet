<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class WebClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Usa tus policies para autorizar CRUD
        $this->authorizeResource(Client::class, 'client');
    }

    // GET /clients-web
    public function index(Request $request)
    {
        $q = $request->query('q');
        $clients = Client::when($q, fn($qq)=>
                $qq->where('name','like',"%$q%")
                   ->orWhere('email','like',"%$q%")
            )
            ->orderByDesc('id')
            ->paginate(05);

        return view('clients.index', compact('clients','q'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        Client::create($data);
        return redirect()->route('clients.web.index')->with('status','Cliente creado');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        $client->update($data);
        return redirect()->route('clients.web.index')->with('status','Cliente actualizado');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('status','Cliente eliminado');
    }
}
