<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f0f9ff; /* blue-50 */
            margin: 0;
            padding: 0;
            width: 100%;
            -webkit-text-size-adjust: none;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px; /* rounded-2xl */
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .header {
            background: linear-gradient(135deg, #2563EB 0%, #06B6D4 100%); /* blue-600 to cyan-500 */
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin-top: 10px;
        }
        .content {
            padding: 40px 30px;
            color: #374151; /* gray-700 */
            line-height: 1.6;
            font-size: 16px;
        }
        .footer {
            background-color: #f9fafb; /* gray-50 */
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280; /* gray-500 */
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #2563EB 0%, #06B6D4 100%);
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 25px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NexaCore</h1>
            <div class="subtitle">@yield('title', 'Notificación del Sistema')</div>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p style="margin-bottom: 10px;">&copy; {{ date('Y') }} Departamento de Sistemas - Clínica.</p>
            <p style="margin: 0;">Este es un mensaje automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
