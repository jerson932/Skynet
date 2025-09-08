<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Visita #{{ $visit->id }}</title>
  <style>
    body{ font-family: DejaVu Sans, sans-serif; font-size:12px; }
    h1{ font-size:18px; margin:0 0 10px; }
    .box{ border:1px solid #333; padding:10px; margin-bottom:10px; }
    .row{ margin:4px 0; }
    .label{ font-weight:bold; width:160px; display:inline-block; }
    small{ color:#666; }
  </style>
</head>
<body>
  <h1>Reporte de Visita #{{ $visit->id }}</h1>

  <div class="box">
    <div class="row"><span class="label">Cliente:</span> {{ $visit->client->name }}</div>
    <div class="row"><span class="label">Técnico:</span> {{ $visit->tecnico->name }}</div>
    <div class="row"><span class="label">Supervisor:</span> {{ $visit->supervisor->name }}</div>
    <div class="row"><span class="label">Programada:</span> {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}</div>
  </div>

  <div class="box">
    <div class="row"><span class="label">Check-in:</span> {{ optional($visit->check_in_at)->format('Y-m-d H:i') }}</div>
    <div class="row"><span class="label">Lat/Lng:</span> {{ $visit->check_in_lat }}, {{ $visit->check_in_lng }}</div>
  </div>

  <div class="box">
    <div class="row"><span class="label">Check-out:</span> {{ optional($visit->check_out_at)->format('Y-m-d H:i') }}</div>
    <div class="row"><span class="label">Lat/Lng:</span> {{ $visit->check_out_lat }}, {{ $visit->check_out_lng }}</div>
  </div>

  <div class="box">
    <div class="row"><span class="label">Notas:</span> {{ $visit->notes ?? '—' }}</div>
  </div>

  <small>Generado por {{ config('app.name') }} el {{ now()->format('Y-m-d H:i') }}</small>
</body>
</html>
