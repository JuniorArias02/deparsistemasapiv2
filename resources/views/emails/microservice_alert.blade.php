<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Microservicio</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        @if($status === 'down')
            <h2 style="color: #dc2626; margin-top: 0; border-bottom: 2px solid #fee2e2; padding-bottom: 10px;">
                🚨 ALERTA: Pérdida de Conexión
            </h2>
            <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
                El sistema <strong>NexaCore</strong> ha detectado una pérdida de conexión con el microservicio convertidor de Excel a PDF.
            </p>
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; color: #991b1b;"><strong>URL del Servicio:</strong> {{ $url }}</p>
                @if($errorMessage)
                    <p style="margin: 10px 0 0 0; color: #991b1b;"><strong>Error Detectado:</strong> {{ $errorMessage }}</p>
                @endif
            </div>
            <p style="color: #4b5563; font-size: 14px;">
                Por favor, verifique el estado del servidor externo y asegúrese de que el microservicio esté en ejecución.
                Se enviará una nueva notificación cuando se recupere la conexión.
            </p>
        @else
            <h2 style="color: #16a34a; margin-top: 0; border-bottom: 2px solid #dcfce7; padding-bottom: 10px;">
                ✅ INFO: Conexión Recuperada
            </h2>
            <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
                El sistema <strong>NexaCore</strong> ha restablecido exitosamente la conexión con el microservicio convertidor de Excel a PDF.
            </p>
            <div style="background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; color: #166534;"><strong>URL del Servicio:</strong> {{ $url }}</p>
                <p style="margin: 10px 0 0 0; color: #166534;"><strong>Estado:</strong> Online y respondiendo correctamente.</p>
            </div>
        @endif

        <div style="margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 15px; text-align: center; color: #9ca3af; font-size: 12px;">
            <p>Este es un mensaje automático de monitoreo de NexaCore.</p>
        </div>
    </div>
</body>
</html>
