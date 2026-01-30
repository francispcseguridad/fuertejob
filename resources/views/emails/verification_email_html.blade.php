<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Correo Electrónico</title>
    <style>
        /* Estilos generales para el correo */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #2D3748;
            /* Gris oscuro para profesionalismo */
            color: #ffffff;
            padding: 20px 30px;
            text-align: center;
        }

        .content {
            padding: 30px;
            line-height: 1.6;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #ffffff !important;
            /* Importante para anular estilos del cliente */
            background-color: #4CAF50;
            /* Verde vibrante (similar a 'success') */
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .footer {
            background-color: #eeeeee;
            color: #777777;
            padding: 20px 30px;
            font-size: 12px;
            text-align: center;
        }

        .link-text {
            word-break: break-all;
            font-size: 10px;
            color: #555555;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <center style="width: 100%; background-color: #f4f4f4;">
        <div class="container"
            style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
            <!-- Header -->
            <div class="header"
                style="background-color: #2D3748; color: #ffffff; padding: 20px 30px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">¡Verificación Requerida!</h1>
            </div>

            <!-- Content -->
            <div class="content" style="padding: 30px; line-height: 1.6;">
                <p>Hola,</p>
                <p>Gracias por dar el paso y unirte a nuestra plataforma. Para garantizar la seguridad de tu cuenta y
                    activar todas las funcionalidades, solo necesitamos que confirmes tu dirección de correo.</p>

                <h3 style="margin-top: 30px; color: #2D3748;">Tu cuenta está casi lista.</h3>

                <!-- Botón de Acción -->
                <div class="button-container" style="text-align: center; margin: 30px 0;">
                    <a href="{{ $url }}" class="button"
                        style="display: inline-block; padding: 12px 25px; font-size: 16px; font-weight: bold; color: #ffffff !important; background-color: #4CAF50; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; mso-padding-alt: 0; /* Fix for Outlook */">
                        <!-- Mso Padding es un hack para Outlook, no lo veas como HTML limpio -->
                        <!--[if mso]>
                        <i style="letter-spacing: 25px; mso-font-width: -100%; mso-text-raise: 20pt;">&nbsp;</i><a href="{{ $url }}" style="color: #ffffff; text-decoration: none;"><![endif]-->
                        Confirmar Mi Dirección de Correo
                        <!--[if mso]></a><i style="letter-spacing: 25px; mso-font-width: -100%;">&nbsp;</i><![endif]-->
                    </a>
                </div>

                <p style="border-top: 1px solid #eeeeee; padding-top: 20px; font-size: 14px;">
                    Si tienes problemas para hacer clic en el botón, copia y pega el siguiente enlace en tu navegador:
                </p>
                <span class="link-text"
                    style="word-break: break-all; font-size: 10px; color: #555555; display: block; margin-top: 10px;">{{ $url }}</span>

            </div>

            <!-- Footer -->
            <div class="footer"
                style="background-color: #eeeeee; color: #777777; padding: 20px 30px; font-size: 12px; text-align: center;">
                <p style="margin: 0;">Este enlace expirará en 60 minutos. Si no solicitaste esta verificación, puedes
                    ignorar este mensaje.</p>
                <p style="margin: 5px 0 0 0;">Puedes conocer nuestra Política de Privacidad haciendo clic <a
                        href="https://www.fuertejob.com/info/politica-de-privacidad" target="_blank">aquí</a>.</p>
                <p style="margin: 5px 0 0 0;">© {{ date('Y') }} **{{ config('app.name') }}**. Todos los derechos
                    reservados.</p>
            </div>
        </div>
    </center>
</body>

</html>
