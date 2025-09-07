<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // GET /api/clients

       public function __construct()
{
    // Aplica ClientPolicy a los métodos REST del recurso
    $this->authorizeResource(\App\Models\Client::class, 'client');
}
    public function index(Request $request)
    {
        // Búsqueda simple por nombre o email (?q=)
        $q = $request->query('q');
        $clients = Client::when($q, fn($query) =>
            $query->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
        )->orderBy('id','desc')->paginate(10);

        return response()->json($clients);
    }

    // POST /api/clients
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

        $client = Client::create($data);

        return response()->json($client, 201);
    }

    // GET /api/clients/{id}
    public function show(Client $client)
    {
        return response()->json($client);
    }

    // PUT/PATCH /api/clients/{id}
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        $client->update($data);

        return response()->json($client);
    }

    // DELETE /api/clients/{id}
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['deleted' => true]);
    }

 
}
