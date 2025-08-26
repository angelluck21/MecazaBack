<!-- resources/views/emails/nueva-reserva-conductor.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nueva Reserva - Mecaza</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e40af; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #1e40af; }
        .highlight { background: #dbeafe; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🚗 Nueva Reserva - Mecaza</h1>
        <p>Hola {{ $data['conductorNombre'] }}, tienes una nueva reserva</p>
    </div>

    <div class="content">
        <div class="highlight">
            <h3>📋 Detalles de la Reserva</h3>
            <p><strong>Fecha de reserva:</strong> {{ $data['fechaReserva'] }}</p>
        </div>

        <div class="info-box">
            <h4>👤 Información del Pasajero</h4>
            <p><strong>Nombre:</strong> {{ $data['pasajeroNombre'] }}</p>
            <p><strong>Asiento:</strong> {{ $data['asiento'] }}</p>
            <p><strong>Ubicación de recogida:</strong> {{ $data['ubicacion'] }}</p>
        </div>

        <div class="info-box">
            <h4>   Detalles del Viaje</h4>
            <p><strong>Placa:</strong> {{ $data['placa'] }}</p>
            <p><strong>Destino:</strong> {{ $data['destino'] }}</p>
            <p><strong>Fecha:</strong> {{ $data['fecha'] }}</p>
            <p><strong>Hora de salida:</strong> {{ $data['hora'] }}</p>
        </div>

        <div class="highlight">
            <p><strong>⚠ Acción requerida:</strong> Por favor, confirma o rechaza esta reserva desde tu panel de conductor.</p>
        </div>
    </div>

    <div class="footer">
        <p>© 2024 Mecaza - Sistema de Gestión de Viajes</p>
        <p>Este email fue enviado automáticamente, no respondas a este mensaje.</p>
    </div>
</div>
</body>
</html>
<?php
