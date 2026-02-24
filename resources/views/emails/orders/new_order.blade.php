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
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Sede:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->sede->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Tipo de Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->tipoSolicitud->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Fecha Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y h:i A') }}</td>
            </tr>
            @if($pedido->observacion)
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Observación:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->observacion }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Lista de Items -->
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 10px; font-size: 16px; color: #334155;">Items del Pedido</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="text-align: left; padding: 10px; border-radius: 6px 0 0 6px; color: #475569;">Item</th>
                    <th style="text-align: center; padding: 10px; color: #475569;">Cant.</th>
                    <th style="text-align: center; padding: 10px; border-radius: 0 6px 6px 0; color: #475569;">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->items as $item)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; color: #1e293b;">{{ $item->nombre }}</td>
                    <td style="text-align: center; padding: 10px; color: #1e293b;">{{ $item->cantidad }}</td>
                    <td style="text-align: center; padding: 10px; color: #64748b;">{{ $item->unidad_medida }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Por favor, ingresa al sistema para gestionar esta solicitud.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/cp-pedidos/{{ $pedido->id }}" class="button" style="color: #ffffff;">Revisar Pedido</a>
    </div>
@endsection
