@extends('emails.layout')

@section('title', 'Nuevo Pedido por Revisar')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>Equipo de Compras</strong> 👋</p>
    
    <p style="margin-bottom: 25px;">
        Se ha generado un nuevo pedido de compra en el sistema que requiere tu revisión.
    </p>

    <!-- Resumen del Pedido -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Detalles del Pedido</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px; width: 140px;">Consecutivo:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">#{{ $pedido->consecutivo }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Solicitante:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->solicitante->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Fecha Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Items:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->items->count() }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Por favor, ingresa al sistema para aprobar o rechazar esta solicitud.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/cp-pedidos/{{ $pedido->id }}" class="button">Revisar Pedido</a>
    </div>
@endsection
