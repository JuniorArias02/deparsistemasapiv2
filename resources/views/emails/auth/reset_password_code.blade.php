@extends('emails.layout')

@section('title', 'Recuperación de Contraseña')

@section('content')
    <p style="font-size: 18px; margin-bottom: 20px;">Hola, <strong>{{ $user->nombre_completo ?? $user->usuario }}</strong>.</p>
    
    <p style="color: #666; margin-bottom: 30px;">
        Has solicitado restablecer tu contraseña. Usa el siguiente código de verificación para continuar:
    </p>

    <div style="background-color: #f3f4f6; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 30px;">
        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4f46e5;">
            {{ $code }}
        </span>
    </div>

    <p style="font-size: 14px; color: #999; margin-bottom: 0;">
        Este código expirará en 15 minutos.<br>
        Si no solicitaste este cambio, puedes ignorar este correo.
    </p>
@endsection
