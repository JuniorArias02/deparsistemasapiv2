@extends('emails.layout')

@section('title', 'Pedido Rechazado')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>{{ $pedido->creador->nombre_completo ?? 'Usuario' }}</strong>.</p>
    
    <p style="margin-bottom: 25px;">
        Te informamos que tu pedido con consecutivo <strong>#{{ $pedido->consecutivo }}</strong> ha sido objeto de una revisión y <strong style="color: #be123c;">no ha sido aprobado</strong> por el departamento de compras.
    </p>

    <!-- Card de Motivo -->
    <div style="background-color: #fff1f2; border-left: 4px solid #f43f5e; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 16px; color: #9f1239;">Motivo del Rechazo:</h3>
        <p style="margin: 0; color: #881337; line-height: 1.5;">{{ $motivo ?? $pedido->observaciones_pedidos ?? 'No especificado' }}</p>
    </div>
    
    <!-- Resumen -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px; width: 140px;">Fecha Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Tipo Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->tipoSolicitud->nombre ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Por favor, revisa las observaciones y, si es necesario, genera un nuevo pedido con las correcciones indicadas.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/cp-pedidos/{{ $pedido->id }}" class="button" style="background: #ef4444; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);">Ver Pedido</a>
    </div>
@endsection
