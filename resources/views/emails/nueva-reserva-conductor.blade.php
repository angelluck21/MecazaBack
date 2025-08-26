<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Reserva de Viaje</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .reserva-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .label {
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚗 Nueva Reserva de Viaje</h1>
        <p>Mecaza - Sistema de Reservas</p>
    </div>

    <div class="content">
        <h2>Hola {{ $conductorNombre }},</h2>
        
        <p>Has recibido una nueva reserva para tu viaje. Aquí están los detalles:</p>

        <div class="reserva-info">
            <h3>📋 Información de la Reserva</h3>
            <p><span class="label">Pasajero:</span> {{ $pasajeroNombre }}</p>
            <p><span class="label">Asiento:</span> {{ $asiento }}</p>
            <p><span class="label">Ubicación de Recogida:</span> {{ $ubicacion }}</p>
            <p><span class="label">Fecha de Reserva:</span> {{ $fechaReserva }}</p>
        </div>

        <div class="reserva-info">
            <h3>🚙 Detalles del Viaje</h3>
            <p><span class="label">Placa del Vehículo:</span> {{ $placa }}</p>
            <p><span class="label">Destino:</span> {{ $destino }}</p>
            <p><span class="label">Fecha del Viaje:</span> {{ $fecha }}</p>
            <p><span class="label">Hora de Salida:</span> {{ $hora }}</p>
        </div>

        <p><strong>Por favor, confirma esta reserva y contacta al pasajero si es necesario.</strong></p>

        <p>Gracias por usar Mecaza.</p>
    </div>

    <div class="footer">
        <p>Este es un email automático del sistema Mecaza.</p>
        <p>No respondas a este email.</p>
    </div>
</body>
</html>
