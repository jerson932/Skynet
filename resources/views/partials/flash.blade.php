@php
    // Prefer structured flash notifications (Flash service), fallback to session('status') or session('error')
    if (session()->has('flash_notification')) {
        $flash = session('flash_notification');
    } elseif (session()->has('status')) {
        $flash = ['level' => 'success', 'message' => session('status')];
    } elseif (session()->has('error')) {
        $flash = ['level' => 'danger', 'message' => session('error')];
    } else {
        $flash = null;
    }
@endphp

@if($flash)
    @php
        $level = $flash['level'] ?? 'info';
        $colors = [
            'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800'],
            'danger'  => ['bg' => 'bg-red-50',   'border' => 'border-red-200',   'text' => 'text-red-800'],
            'info'    => ['bg' => 'bg-blue-50',  'border' => 'border-blue-200',  'text' => 'text-blue-800'],
        ];
        $c = $colors[$level] ?? $colors['info'];
    @endphp

    <div
        role="status"
        aria-live="polite"
        class="flash fixed top-4 right-4 max-w-sm w-full {{ $c['bg'] }} border {{ $c['border'] }} {{ $c['text'] }} shadow p-4 rounded-md transform transition-all duration-700 opacity-0 translate-y-2"
    >
        <div class="flex items-start">
            <div class="flex-1">
                <div class="font-medium text-sm">{{ ucfirst($level) }}</div>
                <div class="mt-1 text-sm">{!! nl2br(e($flash['message'])) !!}</div>
            </div>
            <div class="ml-3">
                <button type="button" class="flash-close text-sm font-medium focus:outline-none">✕</button>
            </div>
        </div>
    </div>
@endif
