@component('mail::message')
# Tu Reserva ha sido Confirmada

Hola, tu reserva ha sido confirmada exitosamente.

@component('mail::table')
| Campo     | Detalle |
|:----------|:--------|
| PNR       | {{ $data['pnr']    ?? '—' }} |
| Origen    | {{ $data['origen'] ?? '—' }} |
@endcomponent

Gracias por viajar con<br>
{{ config('app.name') }}
@endcomponent
