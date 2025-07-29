<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    </style>
</head>
<body>
<h2> {{ $factura['Viaje'] }}</h2>
<p><strong>Usuario:</strong> {{ $factura['usuario'] }}</p>
<p><strong>Conductor:</strong> {{ $factura['conductor'] }}</p>
<p><strong>Carro:</strong> {{ $factura['carro'] }}</p>
<p><strong>Destino:</strong> {{ $factura['destino'] }}</p>
<p><strong>Precio:</strong> {{ $factura['precio'] }}</p>
<p><strong>Fecha:</strong> {{ $factura['fecha'] }}</p>
<hr>
<p>{{ $factura['detalles'] }}</p>
</body>
</html>

<?php
