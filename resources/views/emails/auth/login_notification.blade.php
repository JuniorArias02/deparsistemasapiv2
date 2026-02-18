@extends('emails.layout')

@section('title', 'Nuevo Inicio de Sesión Detectado')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>{{ $user->nombre_completo }}</strong> 👋</p>
    
    <p style="margin-bottom: 25px;">Hemos detectado un nuevo inicio de sesión en tu cuenta. Aquí están los detalles:</p>
    
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #64748b; font-size: 14px; width: 140px;">Dispositivo:</td>
                <td style="padding: 8px 0; color: #1e293b; font-weight: 600;">{{ $os }} — {{ $browser }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Dirección IP:</td>
                <td style="padding: 8px 0; color: #1e293b; font-weight: 600;">{{ $details['ip'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #64748b; font-size: 14px;">Fecha y Hora:</td>
                <td style="padding: 8px 0; color: #1e293b; font-weight: 600;">{{ $details['time'] }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fff1f2; border-left: 4px solid #f43f5e; padding: 15px; border-radius: 4px;">
        <p style="margin: 0; color: #9f1239; font-size: 14px;">
            <strong>¿No fuiste tú?</strong><br>
            Si no reconoces esta actividad, por favor contacta inmediatamente al administrador del sistema.
        </p>
    </div>
    
    <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
        Atentamente,<br>
        El equipo de Sistemas
    </p>
@endsection
