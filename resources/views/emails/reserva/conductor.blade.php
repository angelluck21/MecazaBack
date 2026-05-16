@component('mail::message')
# Nueva Reserva de Pasajero

Hola **{{ $data['conductor'] ?? 'Conductor' }}**,

Un pasajero ha reservado un asiento en tu vehículo.

@component('mail::table')
| Campo        | Detalle |
|:-------------|:--------|
| Pasajero     | {{ $data['pasajero']     ?? '—' }} |
| Teléfono     | {{ $data['telefono']     ?? '—' }} |
| Ubicación    | {{ $data['ubicacion']    ?? '—' }} |
| Asiento      | {{ $data['asiento']      ?? '—' }} |
| Placa        | {{ $data['placa']        ?? '—' }} |
| Destino      | {{ $data['destino']      ?? '—' }} |
| Fecha        | {{ $data['fecha']        ?? '—' }} |
| Hora salida  | {{ $data['horasalida']   ?? '—' }} |
| Fecha reserva| {{ $data['fecha_reserva'] ?? '—' }} |
@endcomponent

Revisa tu panel de conductor para gestionar esta reserva.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
