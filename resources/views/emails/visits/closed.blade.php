@component('mail::message')
# Visita finalizada

**Cliente:** {{ $visit->client->name }}  
**Técnico:** {{ $visit->tecnico->name }}  
**Programada:** {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}

@component('mail::panel')
**Check-in:** {{ optional($visit->check_in_at)->format('Y-m-d H:i') }}  
Lat/Lng: {{ $visit->check_in_lat }}, {{ $visit->check_in_lng }}

**Check-out:** {{ optional($visit->check_out_at)->format('Y-m-d H:i') }}  
Lat/Lng: {{ $visit->check_out_lat }}, {{ $visit->check_out_lng }}
@endcomponent

**Notas:**  
{{ $visit->notes ?? '—' }}

@component('mail::button', ['url' => 'https://www.google.com/maps/dir/?api=1&destination='.$visit->check_out_lat.','.$visit->check_out_lng])
Ver ubicación de salida
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
