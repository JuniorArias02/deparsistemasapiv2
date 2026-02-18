@extends('emails.layout')

@section('title', 'Pedido Aprobado por Gerencia')

{{-- Override styles for Admin Palette (Purple/Pink) --}}
@section('styles')
<style>
    .header {
        background: linear-gradient(135deg, #4f46e5 0%, #db2777 100%) !important; /* indigo-600 via purple-600 to pink-600 */
    }
    .button {
        background: linear-gradient(135deg, #4f46e5 0%, #db2777 100%) !important;
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2) !important;
    }
</style>
@endsection

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>{{ $pedido->creador->nombre_completo ?? 'Usuario' }}</strong> ✨</p>
    
    <p style="margin-bottom: 25px;">
        ¡Buenas noticias! Tu pedido con consecutivo <strong>#{{ $pedido->consecutivo }}</strong> ha recibido la <strong>aprobación final de la Gerencia</strong>.
    </p>

    <!-- Observación Gerencia -->
    @if($pedido->observacion_gerencia)
    <div style="background-color: #fdf4ff; border-left: 4px solid #d946ef; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #86198f; font-size: 14px;"><strong>Observación de Gerencia:</strong></p>
        <p style="margin: 5px 0 0 0; color: #701a75;">{{ $pedido->observacion_gerencia }}</p>
    </div>
    @endif
    
    <!-- Resumen del Pedido -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Detalles de la Aprobación</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px; width: 140px;">Fecha Aprobación:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ now()->format('d/m/Y H:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Total Items:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $pedido->items->count() }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        El proceso de compra iniciará a la brevedad según los lineamientos establecidos.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/cp-pedidos/{{ $pedido->id }}" class="button">Ver Pedido Aprobado</a>
    </div>
@endsection
