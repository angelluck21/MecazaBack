<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: white;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
        }
        .company-info h1 {
            font-size: 28px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .company-info p {
            font-size: 12px;
            color: #666;
            margin: 2px 0;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 24px;
            color: #1e40af;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background: #1e40af;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .row-label {
            font-weight: bold;
            color: #333;
            width: 35%;
        }
        .row-value {
            color: #555;
            text-align: right;
            width: 60%;
        }
        .separator {
            border-bottom: 1px solid #ddd;
            margin: 15px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .details-table th {
            background: #1e40af;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .details-table td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        .details-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .totals {
            float: right;
            width: 40%;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
        }
        .total-row.final {
            background: #1e40af;
            color: white;
            border: none;
            font-weight: bold;
            font-size: 16px;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 11px;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>🚗 MECAZA</h1>
                <p><strong>Empresa de Transporte Compartido</strong></p>
                <p>NIT: 123456789-0</p>
                <p>Teléfono: +57 300 000 0000</p>
                <p>Email: contacto@mecaza.com</p>
            </div>
            <div class="invoice-title">
                <h2>FACTURA</h2>
                <div class="invoice-number">
                    <strong>{{ $factura->numero_factura }}</strong>
                </div>
                <div class="invoice-number">
                    <strong>Fecha:</strong> {{ $factura->fecha_emision->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
            <div class="row">
                <div class="row-label">Nombre:</div>
                <div class="row-value">{{ $usuario->name ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Email:</div>
                <div class="row-value">{{ $usuario->email ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Teléfono:</div>
                <div class="row-value">{{ $usuario->tel ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">ID Usuario:</div>
                <div class="row-value">#{{ $usuario->id_users }}</div>
            </div>
        </div>

        <!-- Información del Viaje -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DEL VIAJE</div>
            <div class="row">
                <div class="row-label">Conductor:</div>
                <div class="row-value">{{ $carro->conductor ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Placa Vehículo:</div>
                <div class="row-value">{{ $carro->placa ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Destino:</div>
                <div class="row-value">{{ $factura->destino ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Fecha del Viaje:</div>
                <div class="row-value">{{ $carro->fecha ? \Carbon\Carbon::parse($carro->fecha)->format('d/m/Y') : 'No especificada' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Hora Salida:</div>
                <div class="row-value">{{ $carro->horasalida ?? 'No especificada' }}</div>
            </div>
            <div class="row">
                <div class="row-label">Asiento Reservado:</div>
                <div class="row-value">{{ $reserva->asiento ?? 'No especificado' }}</div>
            </div>
        </div>

        <!-- Detalles de Pago -->
        <table class="details-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align: right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pasaje de Transporte - Viaje {{ $factura->numero_factura }}</td>
                    <td style="text-align: right;">${{ number_format($factura->subtotal, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($factura->subtotal, 2, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>IVA (19%):</span>
                <span>${{ number_format($factura->impuesto, 2, ',', '.') }}</span>
            </div>
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>${{ number_format($factura->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="clear"></div>

        <!-- Información de Pago -->
        <div class="section">
            <div class="section-title">CONDICIONES DE PAGO</div>
            <div class="row">
                <div class="row-label">Estado:</div>
                <div class="row-value"><strong>PAGADO</strong></div>
            </div>
            <div class="row">
                <div class="row-label">Método:</div>
                <div class="row-value">Pago Online / Transferencia Bancaria</div>
            </div>
            <div class="row">
                <div class="row-label">Referencia:</div>
                <div class="row-value">{{ $reserva->id_reservarviajes }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Esta factura fue emitida por MECAZA - Sistema de Transporte Compartido</p>
            <p>Documento generado automáticamente. No requiere firma digital.</p>
            <p>Para consultas, contacte a: contacto@mecaza.com | Teléfono: +57 300 000 0000</p>
            <p style="margin-top: 15px; color: #999;">
                Gracias por usar nuestro servicio. ¡Buen viaje!
            </p>
        </div>
    </div>
</body>
</html>
