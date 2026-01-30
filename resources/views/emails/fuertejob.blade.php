<!--
    Plantilla de Email FuerteJob (emails.notificacion-fuertejob) - Versión Experto Webmaster y RGPD

    Diseño minimalista y elegante, con énfasis en la legibilidad, seriedad (corporativo) y cumplimiento RGPD.
    Utiliza los datos de la tabla 'portal_settings' pasados como la variable $settings.
    Variables disponibles: $nombre, $asunto, $email (email de quien contacta/interesado), $mensaje, $settings.
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $asunto }}</title>
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

        /* Enlaces en color primario (Indigo) */

        /* Estilos del cuerpo y contenedor */
        .bodyContent {
            background-color: #f7f9fc;
            /* Fondo muy claro, casi blanco-azulado */
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e0e7ff;
            /* Borde muy sutil para definir el contenedor */
        }

        /* Encabezado: Logo y Nombre */
        .header {
            background-color: #ffffff;
            padding: 30px;
            text-align: left;
            border-bottom: 2px solid #4F46E5;
            /* Línea gruesa color corporativo */
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

        .content p {
            font-size: 16px;
            line-height: 1.8;
            color: #374151;
            /* Texto oscuro y legible */
            margin-bottom: 20px;
        }

        /* Separador Elegante */
        .separator {
            width: 100%;
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }

        /* Bloque de Información de Datos */
        .data-table td {
            padding: 12px 0;
            font-size: 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .data-table .label {
            font-weight: 600;
            color: #1F2937;
            width: 35%;
        }

        .data-table .value {
            color: #4B5563;
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
                                <h2>{{ $asunto ?? 'Notificación FuerteJob' }}</h2>
                                <div class="separator"></div>
                                <div
                                    style="background-color: #F9FAFB; padding: 22px; border-radius: 6px; border-left: 4px solid #4F46E5;">
                                    {!! $bodyContent ?? ($mensaje ?? '<p>No se ha proporcionado contenido.</p>') !!}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="footer">
                                <p style="font-weight: 600; color: #1F2937; margin-bottom: 5px;">
                                    FuerteJob
                                </p>
                                <p style="margin-bottom: 5px;">
                                    Contacto y Soporte: <a href="mailto:info@fuertejob.com"
                                        style="color: #4F46E5;">info@fuertejob.com</a </p>
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
