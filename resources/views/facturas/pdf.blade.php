<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: white; color: #333; font-size: 12px; }
        .container { width: 100%; max-width: 720px; margin: 0 auto; padding: 36px 24px; }

        /* Header */
        .header { width: 100%; border-bottom: 3px solid #1e40af; padding-bottom: 16px; margin-bottom: 28px; }
        .header-left  { float: left; width: 55%; }
        .header-right { float: right; width: 40%; text-align: right; }
        .brand  { font-size: 26px; color: #1e40af; font-weight: 800; margin-bottom: 4px; }
        .brand-sub { font-size: 11px; color: #666; }
        .inv-title  { font-size: 22px; color: #1e40af; font-weight: 700; }
        .inv-num    { font-size: 12px; color: #555; margin-top: 4px; }
        .clear { clear: both; }

        /* Section */
        .section { margin-bottom: 22px; }
        .section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: #fff; background: #1e40af;
            padding: 6px 10px; margin-bottom: 10px; border-radius: 3px;
        }

        /* Rows */
        .row { margin-bottom: 6px; }
        .row-label { display: inline-block; font-weight: 700; width: 38%; color: #333; }
        .row-value  { display: inline-block; color: #555; }

        /* Table */
        .details-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .details-table th {
            background: #1e40af; color: white;
            padding: 8px 10px; text-align: left; font-size: 11px; border: 1px solid #ddd;
        }
        .details-table td { padding: 8px 10px; border: 1px solid #ddd; font-size: 11px; }
        .details-table tr:nth-child(even) { background: #f9fafb; }
        .td-right { text-align: right; }

        /* Totals */
        .totals-table { width: 42%; float: right; margin-top: 16px; border-collapse: collapse; }
        .totals-table td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .totals-table .label { color: #555; }
        .totals-table .value { text-align: right; font-weight: 600; color: #1e293b; }
        .total-final { background: #1e40af; color: white !important; font-size: 14px; font-weight: 800; }
        .total-final td { border: none !important; padding: 10px 10px !important; }

        /* Footer */
        .footer {
            text-align: center; margin-top: 44px; padding-top: 16px;
            border-top: 1px solid #ddd; color: #888; font-size: 10px; line-height: 1.7;
        }
        .badge-paid {
            display: inline-block; background: #dcfce7; color: #15803d;
            border: 1px solid #bbf7d0; padding: 3px 12px; border-radius: 20px;
            font-weight: 700; font-size: 11px; margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div class="container">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="brand">MECAZA</div>
            <div class="brand-sub">Plataforma de Transporte Compartido</div>
            <div class="brand-sub" style="margin-top:6px;">contacto@mecaza.com</div>
        </div>
        <div class="header-right">
            <div class="inv-title">FACTURA</div>
            <div class="inv-num"><strong>{{ $factura->numero_factura }}</strong></div>
            <div class="inv-num" style="margin-top:4px;">
                Emitida: {{ $factura->fecha_emision ? \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="clear"></div>
    </div>

    {{-- Cliente --}}
    <div class="section">
        <div class="section-title">Información del pasajero</div>
        <div class="row">
            <span class="row-label">Nombre:</span>
            <span class="row-value">{{ $usuario->name ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Correo:</span>
            <span class="row-value">{{ $usuario->email ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Teléfono:</span>
            <span class="row-value">{{ $usuario->tel ?? '—' }}</span>
        </div>
    </div>

    {{-- Viaje --}}
    <div class="section">
        <div class="section-title">Detalle del viaje</div>
        <div class="row">
            <span class="row-label">Conductor:</span>
            <span class="row-value">{{ $carro->conductor ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Placa:</span>
            <span class="row-value">{{ $carro->placa ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Origen:</span>
            <span class="row-value">{{ $carro->origen ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Destino:</span>
            <span class="row-value">{{ $factura->destino ?? $carro->destino ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Fecha del viaje:</span>
            <span class="row-value">
                {{ $carro->fecha ? \Carbon\Carbon::parse($carro->fecha)->format('d/m/Y') : '—' }}
            </span>
        </div>
        <div class="row">
            <span class="row-label">Hora de salida:</span>
            <span class="row-value">{{ $carro->horasalida ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="row-label">Asiento:</span>
            <span class="row-value">#{{ $reserva->asiento ?? '—' }}</span>
        </div>
    </div>

    {{-- Servicios --}}
    <div class="section">
        <div class="section-title">Servicios facturados</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align:right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pasaje compartido — {{ $carro->origen ?? '' }} → {{ $factura->destino ?? $carro->destino ?? '' }}</td>
                    <td class="td-right">${{ number_format($factura->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Totales --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">${{ number_format($factura->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">IVA (19%)</td>
            <td class="value">${{ number_format($factura->impuesto, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-final">
            <td>TOTAL</td>
            <td style="text-align:right;">${{ number_format($factura->total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="clear"></div>

    {{-- Estado de pago --}}
    <div class="section" style="margin-top:20px;">
        <div class="section-title">Estado del pago</div>
        <div class="row">
            <span class="row-label">Estado:</span>
            <span class="row-value">
                <span class="badge-paid">PAGADO</span>
            </span>
        </div>
        <div class="row">
            <span class="row-label">Referencia:</span>
            <span class="row-value">#{{ $reserva->id_reservarviajes ?? '—' }}</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>MECAZA — Plataforma de Transporte Compartido</p>
        <p>Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}. No requiere firma.</p>
        <p style="margin-top:8px;color:#bbb;">¡Gracias por viajar con nosotros!</p>
    </div>

</div>
</body>
</html>
