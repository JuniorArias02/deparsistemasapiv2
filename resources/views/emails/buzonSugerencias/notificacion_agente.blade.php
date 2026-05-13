@extends('emails.layout')

@section('title', 'Nuevo Ticket Generado')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>Agente de Soporte</strong> 👋</p>
    
    <p style="margin-bottom: 25px;">
        Se ha generado un nuevo ticket en el Buzón de Sugerencias que requiere tu atención.
    </p>

    <!-- Resumen del Ticket -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Detalles del Ticket</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px; width: 140px;">Código Ticket:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">#{{ $sugerencia->codigo_ticket }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Usuario:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $sugerencia->creador->nombre_completo }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Asunto:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $sugerencia->asunto }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #64748b; font-size: 14px;">Prioridad:</td>
                <td style="padding: 5px 0; color: #1e293b; font-weight: 600;">{{ $sugerencia->prioridad }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 15px; margin-bottom: 30px;">
        <p style="margin: 0; color: #92400e; font-size: 14px;">
            <strong>Observaciones:</strong><br>
            {{ $sugerencia->observaciones }}
        </p>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Por favor, ingresa al sistema para gestionar esta solicitud.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ env('FRONTEND_URL', 'http://localhost:5173') }}/buzon-sugerencias/{{ $sugerencia->id }}" class="button" style="color: #ffffff;">Gestionar Ticket</a>
    </div>
@endsection
