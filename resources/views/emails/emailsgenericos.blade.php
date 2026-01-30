<!--
    Plantilla de Email FuerteJob (emails.fuertejob) - Versión Base para Inyección de Contenido
    Diseño minimalista, elegante, corporativo y con cumplimiento RGPD.
    Recibe el contenido principal del email a través de la variable $bodyContent, que viene de la BD.
    Variables de Configuración (Portal Settings) pasadas: $settings
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $subject ?? 'Notificación FuerteJob' }}</title>
    <style type="text/css">
        /* Reset CSS y Tipografía Profesional */
        body,
        #bodyTable,
        #bodyCell {
            height: 100% !important;
            margin: 0;
            padding: 0;
            width: 100% !important;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        table {
            border-collapse: collapse;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img,
        a img {
            border: 0;
            outline: none;
            text-decoration: none;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            padding: 0;
        }

        p {
            margin: 0;
            padding: 0;
        }

        a {
            color: #4F46E5;
            text-decoration: none;
        }

        /* Estilos del cuerpo y contenedor */
        .bodyContent {
            background-color: #f7f9fc;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e0e7ff;
        }

        /* Encabezado: Logo y Nombre */
        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: left;
            border-bottom: 2px solid #4F46E5;
        }

        /* Contenido Principal */
        .content {
            background-color: #ffffff;
            padding: 30px;
        }

        .content h2 {
            font-size: 24px;
            color: #1F2937;
            margin-bottom: 25px;
            font-weight: 700;
        }

        /* Estilo para Botones dentro del contenido (usados en la plantilla de BD) */
        .button-link {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #4F46E5;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        /* Pie de página (RGPD y Contacto) */
        .footer {
            background-color: #f7f9fc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e7ff;
        }

        .footer p {
            font-size: 12px;
            line-height: 1.7;
            color: #6B7280;
            margin-bottom: 15px;
        }

        .footer a {
            color: #4F46E5;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <center>
        <table border="0" cellpadding="0" cellspacing="0" id="bodyTable" width="100%"
            style="background-color: #f7f9fc;">
            <tr>
                <td align="center" id="bodyCell">
                    <!-- Contenedor Principal -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="container"
                        style="max-width: 600px;">
                        <tr>
                            <td class="header">
                                <!-- Logo de FuerteJob -->
                                @if (isset($settings->logo_url) && $settings->logo_url)
                                    <img src="https://www.fuertejob.com/{{ $settings->logo_url }}" alt="FuerteJob"
                                        width="150"
                                        style="display: block; border: 0; max-width: 200px; height: auto;" />
                                @else
                                    <span
                                        style="font-size: 26px; font-weight: 800; color: #4F46E5; text-transform: uppercase;">
                                        FuerteJob
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="content">
                                {{--
                                    Aquí se inyecta el contenido dinámico del email (viene de la BD,
                                    ya pre-procesado con variables como $user_name o $verification_link)
                                --}}
                                {!! $bodyContent !!}
                            </td>
                        </tr>
                        <tr>
                            <td class="footer">
                                {{-- Se asume que $settings está disponible para las vistas de correo --}}
                                @php
                                    $settings =
                                        $settings ??
                                        (object) [
                                            'legal_name' => 'FuerteJob Portal S.L.',
                                            'contact_email' => 'info@fuertejob.com',
                                            'contact_phone' => '+34 928 00 00 00',
                                            'legal_email' => 'info@fuertejob.com',
                                            'privacy_policy_url' => '#',
                                        ];
                                @endphp

                                <p style="font-weight: 600; color: #1F2937; margin-bottom: 5px;">
                                    com</p>
                                <p style="margin-bottom: 5px;">
                                    Contacto y Soporte: <a href="mailto:info@fuertejob.com"
                                        style="color: #4F46E5;">info@fuertejob.com</a>                                    
                                </p>
                                <p style="margin-top: 15px; border-top: 1px dashed #D1D5DB; padding-top: 15px;">
                                    Recibes esta comunicación al amparo de nuestra Política de Privacidad y en
                                    cumplimiento del Reglamento (UE) 2016/679 del
                                    Parlamento Europeo y del Consejo de 27 de
                                    Abril de 2016 y a la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos
                                    Personales y Garantía de los Derechos
                                    Digitales. Si lo desea, podrá usted ejercitar los derechos
                                    de establecidos en la Política de Privacidad,
                                    enviando un mensaje a la siguiente dirección de correo electrónico: <a
                                        href="mailto:info@fuertejob.com" style="color: #4F46E5;">info@fuertejob.com</a>
                                    , indicando en la línea de “Asunto” el derecho que desea ejercitar.
                                    Para más detalles, consulte nuestra <a
                                        href="https://www.fuertejob.com/info/politica-de-privacidad"
                                        style="color: #4F46E5;">Política de Privacidad</a>.
                                </p>
                                <p>&copy; {{ date('Y') }} FuerteJob. Todos los derechos reservados.</p>
                            </td>
                        </tr>
                    </table>
                    <!-- Fin Contenedor Principal -->
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
