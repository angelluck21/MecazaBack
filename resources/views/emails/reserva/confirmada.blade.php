@component('mail::message')

@if(($data['estado'] ?? '') === 'Confirmada')
# ¡Tu reserva fue confirmada! 🎉
Hola **{{ $data['usuario_nombre'] ?? $data['origen'] ?? 'Pasajero' }}**, tu viaje está listo.
@else
# Tu reserva fue rechazada
Hola **{{ $data['usuario_nombre'] ?? $data['origen'] ?? 'Pasajero' }}**, lamentablemente tu reserva no pudo ser aceptada.
@endif

@component('mail::table')
| Detalle        | Información |
|:---------------|:------------|
| Reserva #      | {{ $data['pnr'] ?? '—' }} |
| Estado         | {{ $data['estado'] ?? '—' }} |
| Conductor      | {{ $data['conductor'] ?? '—' }} |
| Destino        | {{ $data['destino'] ?? '—' }} |
| Fecha          | {{ $data['fecha'] ?? '—' }} |
| Hora de salida | {{ $data['hora'] ?? '—' }} |
| Asiento        | {{ $data['asiento'] ?? '—' }} |
| Placa          | {{ $data['placa'] ?? '—' }} |
@endcomponent

@if(($data['estado'] ?? '') === 'Confirmada')
@component('mail::button', ['url' => '', 'color' => 'success'])
Ver mis reservas
@endcomponent
@endif

Gracias por viajar con **{{ config('app.name') }}**
@endcomponent
