@extends('emails.layout')

@section('title', 'Pedido Aprobado por Compras')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>{{ $pedido->creador->nombre_completo ?? 'Usuario' }}</strong> 👋</p>
    
    <p style="margin-bottom: 25px;">
        Tu pedido con consecutivo <strong>#{{ $pedido->consecutivo }}</strong> ha sido aprobado por el departamento de compras.
    </p>

    <!-- Motivo Aprobación -->
    @if($pedido->motivo_aprobacion)
    <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #166534; font-size: 14px;"><strong>Motivo / Observación:</strong></p>
        <p style="margin: 5px 0 0 0; color: #14532d;">{{ $pedido->motivo_aprobacion }}</p>
    </div>
    @endif
    
    <!-- Detalles del Pedido -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Resumen del Pedido</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px; width: 140px;">Fecha Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Tipo Solicitud:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->tipoSolicitud->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Items Aprobados:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->items->where('comprado', 1)->count() }} / {{ $pedido->items->count() }}</td>
            </tr>
        </table>
    </div>

    <!-- Lista de Items -->
    @if($pedido->items->where('comprado', 1)->count() > 0)
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 10px; font-size: 16px; color: #334155;">Items Aprobados</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="text-align: left; padding: 10px; border-radius: 6px 0 0 6px; color: #475569;">Item</th>
                    <th style="text-align: center; padding: 10px; color: #475569;">Cant.</th>
                    <th style="text-align: center; padding: 10px; border-radius: 0 6px 6px 0; color: #475569;">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->items->where('comprado', 1) as $item)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; color: #1e293b;">{{ $item->nombre }}</td>
                    <td style="text-align: center; padding: 10px; color: #1e293b;">{{ $item->cantidad }}</td>
                    <td style="text-align: center; padding: 10px; color: #64748b;">{{ $item->unidad_medida }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Este pedido continuará su proceso hacia la gerencia para su aprobación final.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/cp-pedidos/{{ $pedido->id }}" class="button">Ver Pedido Completo</a>
    </div>
@endsection
