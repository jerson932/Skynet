@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
        <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Usuarios</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded shadow">Crear usuario</a>
            @if(Auth::user()->isAdmin())
                <button id="btn-mail-test" class="px-4 py-2 bg-purple-600 text-white rounded shadow">Enviar correo de prueba</button>
            @endif
        </div>
    </div>
    <div id="mail-test-toast" class="hidden fixed bottom-6 right-6 bg-white border rounded shadow p-3"></div>

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">#</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Nombre</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Email</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Rol</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Supervisor</th>
                    <th class="px-4 py-2 text-right text-sm text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @foreach($users as $u)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $u->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ optional($u->role)->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ optional($u->supervisor)->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-right">
                        @if(Auth::user()->isAdmin())
                            @if(optional($u->role)->slug === 'supervisor')
                                <a href="{{ route('settings.assign', $u) }}" class="inline-block px-3 py-1 bg-green-500 text-white rounded text-sm mr-2">Asignar técnicos</a>
                            @endif
                            <a href="{{ route('settings.edit', $u) }}" class="inline-block px-3 py-1 bg-indigo-500 text-white rounded text-sm mr-2">Editar</a>

                            <form action="{{ route('settings.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar usuario?');">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 bg-red-500 text-white rounded text-sm mr-2">Eliminar</button>
                            </form>

                            <form action="{{ route('settings.reset_password', $u) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">Resetear contraseña</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('btn-mail-test');
    const toast = document.getElementById('mail-test-toast');
    if (!btn) return;

    btn.addEventListener('click', async function(){
        btn.disabled = true; btn.textContent = 'Enviando...';
        try {
            const res = await fetch('/debug/mail-test', { credentials: 'same-origin' });
            if (!res.ok) {
                const err = await res.json().catch(()=>({message:'Error desconocido'}));
                showToast('Error: ' + (err.message || JSON.stringify(err)), true);
            } else {
                const j = await res.json();
                showToast('OK: correo enviado a ' + j.to);
            }
        } catch (e) {
            showToast('Error de red: ' + e.message, true);
        } finally {
            btn.disabled = false; btn.textContent = 'Enviar correo de prueba';
        }
    });

    function showToast(msg, isError=false){
        toast.textContent = msg;
        toast.classList.remove('hidden');
        toast.style.borderLeft = isError ? '4px solid #e53e3e' : '4px solid #10b981';
        setTimeout(()=>{ toast.classList.add('hidden'); }, 7000);
    }
});
</script>
@endpush
