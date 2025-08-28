@component('mail::message')
    # 🚗 Nueva Reserva de Viaje

    Hola *{{ $data['conductor'] }}*, has recibido una nueva reserva para tu vehículo.

    @component('mail::panel')
        ## 📋 Detalles de la Reserva

        *Pasajero:* {{ $data['pasajero'] }}
        *Teléfono:* {{ $data['telefono'] }}
        *Ubicación:* {{ $data['ubicacion'] }}
        *Asiento Seleccionado:* {{ $data['asiento'] }}
        *Comentario:* {{ $data['comentario'] ?? 'Sin comentarios' }}

        ##    Información del Vehículo

        *Placa:* {{ $data['placa'] }}
        *Destino:* {{ $data['destino'] }}
        *Fecha:* {{ $data['fecha'] }}
        *Hora de Salida:* {{ $data['horasalida'] }}
    @endcomponent

    ##    Fecha de la Reserva
    {{ $data['fecha_reserva'] }}

    @component('mail::button', ['url' => config('app.url') . '/conductor'])
        Ver Panel de Conductor
    @endcomponent

    Gracias por usar nuestro sistema de reservas,

    *{{ config('app.name') }}*
@endcomponent
<?php
