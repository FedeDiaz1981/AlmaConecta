@php
    $brand = $appName ?? config('app.name', 'Alma Conecta');
    $primary = '#d7b14a';
    $bg = '#0b1522';
    $card = '#0f1e32';
    $text = '#e7edf6';
    $muted = '#a7b6cc';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
</head>
<body style="margin:0;padding:0;background-color:{{ $bg }};font-family:Arial, Helvetica, sans-serif;color:{{ $text }};">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:{{ $bg }};padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;">
                    <tr>
                        <td style="text-align:center;padding-bottom:18px;">
                            <div style="font-size:20px;font-weight:700;letter-spacing:1px;color:{{ $primary }};">
                                {{ $brand }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:{{ $card }};border:1px solid #1d2d4a;border-radius:16px;padding:28px;">
                            <h1 style="margin:0 0 8px 0;font-size:20px;color:{{ $text }};">Restablecer contraseña</h1>
                            <p style="margin:0 0 16px 0;font-size:14px;color:{{ $muted }};line-height:1.6;">
                                Hola, recibimos una solicitud para restablecer la contraseña de tu cuenta en {{ $brand }}.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:20px 0 16px 0;">
                                <tr>
                                    <td align="center" bgcolor="{{ $primary }}" style="border-radius:10px;">
                                        <a href="{{ $url }}" target="_blank"
                                           style="display:inline-block;padding:12px 20px;color:#1a1a1a;text-decoration:none;font-weight:700;font-size:14px;">
                                            Restablecer contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 10px 0;font-size:13px;color:{{ $muted }};line-height:1.6;">
                                Este enlace vence en {{ $expire }} minutos.
                            </p>
                            <p style="margin:0 0 12px 0;font-size:13px;color:{{ $muted }};line-height:1.6;">
                                Si no solicitaste este cambio, podés ignorar este correo.
                            </p>

                            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #1d2d4a;font-size:12px;color:{{ $muted }};line-height:1.5;">
                                Si el botón no funciona, copiá y pegá este enlace en tu navegador:
                                <div style="word-break:break-all;margin-top:6px;color:{{ $text }};">{{ $url }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;padding-top:16px;font-size:12px;color:#7e8ea6;">
                            © {{ date('Y') }} {{ $brand }}. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
