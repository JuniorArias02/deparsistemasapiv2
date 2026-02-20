@extends('emails.layouts.main')

@section('content')
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; background-color: #eef2ff; border-radius: 50%; width: 64px; height: 64px; line-height: 64px; text-align: center; margin-bottom: 12px;">
        <span style="font-size: 28px;">🔧</span>
    </div>
    <h2 style="margin: 0; font-size: 22px; color: #1f2937; font-weight: 700;">
        Nueva Agenda de Mantenimiento
    </h2>
    <p style="margin: 8px 0 0; color: #6b7280; font-size: 14px;">
        Se te ha asignado un mantenimiento programado
    </p>
</div>

<p style="font-size: 15px; color: #374151;">
    Hola <strong>{{ $assignedUser->nombre_completo }}</strong>,
</p>
<p style="font-size: 15px; color: #374151;">
    <strong>{{ $scheduledBy->nombre_completo }}</strong> te ha asignado una nueva agenda de mantenimiento. A continuación los detalles:
</p>

{{-- Info card --}}
<div style="background-color: #f9fafb; border-radius: 12px; padding: 24px; margin: 24px 0; border: 1px solid #e5e7eb;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; width: 40%;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Título</span>
                <div style="font-size: 15px; color: #111827; font-weight: 600; margin-top: 2px;">{{ $agenda->titulo }}</div>
            </td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Código</span>
                <div style="font-size: 15px; color: #111827; font-weight: 600; margin-top: 2px;">{{ $mantenimiento->codigo ?? 'N/A' }}</div>
            </td>
        </tr>
        @if($agenda->descripcion)
        <tr>
            <td colspan="2" style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Descripción</span>
                <div style="font-size: 14px; color: #374151; margin-top: 2px;">{{ $agenda->descripcion }}</div>
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Fecha Inicio</span>
                <div style="font-size: 14px; color: #374151; margin-top: 2px;">{{ \Carbon\Carbon::parse($agenda->fecha_inicio)->format('d/m/Y h:i A') }}</div>
            </td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Fecha Fin</span>
                <div style="font-size: 14px; color: #374151; margin-top: 2px;">{{ \Carbon\Carbon::parse($agenda->fecha_fin)->format('d/m/Y h:i A') }}</div>
            </td>
        </tr>
        @if($agenda->sede)
        <tr>
            <td colspan="2" style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Sede</span>
                <div style="font-size: 14px; color: #374151; margin-top: 2px;">{{ $agenda->sede->nombre }}</div>
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Agendado por</span>
                <div style="font-size: 14px; color: #374151; margin-top: 2px;">{{ $scheduledBy->nombre_completo }}</div>
            </td>
            <td style="padding: 10px 0; text-align: right;">
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 600;">Estado</span>
                <div style="margin-top: 2px;">
                    <span style="display: inline-block; background-color: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 12px;">
                        ⏳ Pendiente
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url', config('app.url')) }}/mis-mantenimientos"
       class="button"
       style="display: inline-block; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; letter-spacing: 0.02em;">
        Ver Mis Mantenimientos
    </a>
</div>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; margin-top: 24px;">
    <p style="margin: 0; font-size: 13px; color: #92400e;">
        <strong>📌 Recuerda:</strong> Una vez completado el mantenimiento, marca el registro como revisado desde la plataforma.
    </p>
</div>
@endsection
