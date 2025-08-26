<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva</title>
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
            background-color: #28a745;
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
            border-left: 4px solid #28a745;
        }
        .label {
            font-weight: bold;
            color: #28a745;
        }
        .contact-info {
            background-color: #e7f3ff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #b3d9ff;
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
        <h1>✅ Reserva Confirmada</h1>
        <p>Mecaza - Sistema de Reservas</p>
    </div>

    <div class="content">
        <h2>Hola {{ $pasajeroNombre }},</h2>
        
        <p>¡Tu reserva ha sido confirmada exitosamente! Aquí están todos los detalles:</p>

        <div class="reserva-info">
            <h3>📋 Detalles de tu Reserva</h3>
            <p><span class="label">Asiento:</span> {{ $asiento }}</p>
            <p><span class="label">Ubicación de Recogida:</span> {{ $ubicacion }}</p>
            <p><span class="label">Fecha de Reserva:</span> {{ $fechaReserva }}</p>
        </div>

        <div class="reserva-info">
            <h3>🚙 Información del Viaje</h3>
            <p><span class="label">Conductor:</span> {{ $conductorNombre }}</p>
            <p><span class="label">Placa del Vehículo:</span> {{ $placa }}</p>
            <p><span class="label">Destino:</span> {{ $destino }}</p>
            <p><span class="label">Fecha del Viaje:</span> {{ $fecha }}</p>
            <p><span class="label">Hora de Salida:</span> {{ $hora }}</p>
        </div>

        <div class="contact-info">
            <h3>📞 Información de Contacto</h3>
            <p><span class="label">Teléfono del Conductor:</span> {{ $telefonoConductor }}</p>
            <p><strong>Importante:</strong> Contacta al conductor antes del viaje para confirmar la hora exacta de recogida.</p>
        </div>

        <p><strong>¡Disfruta tu viaje con Mecaza!</strong></p>

        <p>Si tienes alguna pregunta o necesitas modificar tu reserva, contacta nuestro servicio al cliente.</p>
    </div>

    <div class="footer">
        <p>Este es un email automático del sistema Mecaza.</p>
        <p>No respondas a este email.</p>
    </div>
</body>
</html>
