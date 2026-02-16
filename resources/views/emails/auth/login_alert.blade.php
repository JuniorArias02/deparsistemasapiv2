@extends('emails.layouts.main')

@section('content')
    <h2 style="color: #4f46e5; margin-bottom: 20px;">Nuevo Inicio de Sesión Detectado</h2>
    
    <p>Hola, <strong>{{ $user->nombres }} {{ $user->apellidos }}</strong>.</p>
    
    <p>Hemos detectado un nuevo inicio de sesión en tu cuenta de <strong>Depart Sistem</strong>.</p>
    
    <div class="info-box">
        <p style="margin: 5px 0;"><strong>Fecha y Hora:</strong> {{ $fecha }}</p>
        <p style="margin: 5px 0;"><strong>Dirección IP:</strong> {{ $ip }}</p>
        <p style="margin: 5px 0;"><strong>Sistema Operativo:</strong> {{ $os ?? 'No detectado' }}</p>
        <p style="margin: 5px 0;"><strong>Navegador:</strong> {{ $browser ?? 'No detectado' }}</p>
    </div>

    <p>Si fuiste tú, puedes ignorar este mensaje.</p>
    
    <p style="color: #ef4444; font-weight: bold;">
        Si NO reconoces esta actividad, por favor contacta al departamento de sistemas inmediatamente o cambia tu contraseña.
    </p>

    <div style="text-align: center;">
        <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/profile" class="button">Ver Mi Cuenta</a>
    </div>
@endsection
