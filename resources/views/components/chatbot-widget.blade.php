<!-- ========================================================================= -->
<!-- WIDGET CHATBOT ASISTENTE DE FUERTEJOB -->
<!-- ========================================================================= -->
<div id="chatbot-container">

    <!-- Ventana de Chat (Inicialmente oculta) -->
    <div id="chat-window" class="flex-column" style="display: none;">

        <!-- Encabezado del Chatbot -->
        <header class="chat-header">
            <h1 class="h6 mb-0 d-flex align-items-center text-white">
                <svg width="32px" height="32px" viewBox="0 0 24 24" data-name="025_SCIENCE" id="_025_SCIENCE"
                    xmlns="http://www.w3.org/2000/svg" fill="#1d486c">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <defs>
                            <style>
                                .cls-1 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path class="cls-1"
                            d="M16,13H8a3,3,0,0,1-3-3V6A3,3,0,0,1,8,3h8a3,3,0,0,1,3,3v4A3,3,0,0,1,16,13ZM8,5A1,1,0,0,0,7,6v4a1,1,0,0,0,1,1h8a1,1,0,0,0,1-1V6a1,1,0,0,0-1-1Z">
                        </path>
                        <path class="cls-1"
                            d="M10,9a1.05,1.05,0,0,1-.71-.29A1,1,0,0,1,10.19,7a.6.6,0,0,1,.19.06.56.56,0,0,1,.17.09l.16.12A1,1,0,0,1,10,9Z">
                        </path>
                        <path class="cls-1"
                            d="M14,9a1,1,0,0,1-.71-1.71,1,1,0,0,1,1.42,1.42,1,1,0,0,1-.16.12.56.56,0,0,1-.17.09.6.6,0,0,1-.19.06Z">
                        </path>
                        <path class="cls-1" d="M12,4a1,1,0,0,1-1-1V2a1,1,0,0,1,2,0V3A1,1,0,0,1,12,4Z"></path>
                        <path class="cls-1" d="M9,22a1,1,0,0,1-1-1V18a1,1,0,0,1,2,0v3A1,1,0,0,1,9,22Z"></path>
                        <path class="cls-1" d="M15,22a1,1,0,0,1-1-1V18a1,1,0,0,1,2,0v3A1,1,0,0,1,15,22Z"></path>
                        <path class="cls-1"
                            d="M15,19H9a1,1,0,0,1-1-1V12a1,1,0,0,1,1-1h6a1,1,0,0,1,1,1v6A1,1,0,0,1,15,19Zm-5-2h4V13H10Z">
                        </path>
                        <path class="cls-1"
                            d="M5,17a1,1,0,0,1-.89-.55,1,1,0,0,1,.44-1.34l4-2a1,1,0,1,1,.9,1.78l-4,2A.93.93,0,0,1,5,17Z">
                        </path>
                        <path class="cls-1"
                            d="M19,17a.93.93,0,0,1-.45-.11l-4-2a1,1,0,1,1,.9-1.78l4,2a1,1,0,0,1,.44,1.34A1,1,0,0,1,19,17Z">
                        </path>
                    </g>
                </svg>
                Asistente FuerteJob
            </h1>
            <!-- Botón para cerrar la ventana de chat -->
            <button id="close-chat-button"
                class="btn btn-sm btn-light p-1 rounded-circle bg-opacity-10 text-white border-0" title="Cerrar Chat">
                <i class="bi bi-x-lg" style="color: #1c476b !important;"></i>
            </button>
        </header>

        <!-- Historial de Mensajes -->
        <div id="chat-history" class="chat-main">
            <!-- Mensaje de Bienvenida del Bot -->
            <div class="bot-message-wrapper">
                <div class="bot-message">
                    <p class="mb-0">¡Ahul! Soy Roque, tu asistente virtual de FuerteJob.<br>
                        Uso IA para orientarte, pero puedo cometer errores. No envíes datos personales o sensibles
                    </p>

                </div>
            </div>
            <!-- Los mensajes generados se insertarán aquí -->
        </div>

        <!-- Formulario de Entrada de Mensajes -->
        <footer class="p-3 border-top bg-white">
            <form id="chat-form" class="d-flex">
                <input type="text" id="user-input" placeholder="Escribe tu pregunta..." required
                    class="form-control me-2" style="font-size: 0.875rem;">
                <button type="submit" id="send-button"
                    class="btn btn-primary d-flex align-items-center justify-content-center text-white"
                    title="Enviar Mensaje">
                    <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.3938 2.20468C3.70395 1.96828 4.12324 1.93374 4.4679 2.1162L21.4679 11.1162C21.7953 11.2895 22 11.6296 22 12C22 12.3704 21.7953 12.7105 21.4679 12.8838L4.4679 21.8838C4.12324 22.0662 3.70395 22.0317 3.3938 21.7953C3.08365 21.5589 2.93922 21.1637 3.02382 20.7831L4.97561 12L3.02382 3.21692C2.93922 2.83623 3.08365 2.44109 3.3938 2.20468ZM6.80218 13L5.44596 19.103L16.9739 13H6.80218ZM16.9739 11H6.80218L5.44596 4.89699L16.9739 11Z"
                                fill="#fff"></path>
                        </g>
                    </svg>
                </button>
            </form>
        </footer>
    </div>

    <!-- Botón Flotante (Siempre visible) -->
    <button id="toggle-chat-button" class="btn btn-primary shadow-lg rounded-circle"
        style="width: 56px; height: 56px; padding: 0; margin-top: 1rem;" title="Abrir Chatbot">
        <!-- Icono de chat -->
        <i id="chat-icon-open" class="bi bi-chat-dots-fill fs-5 text-white"></i>
        <!-- Icono de cerrar (se puede mostrar cuando el chat está abierto) -->
        <i id="chat-icon-close" class="bi bi-x-lg fs-5 d-none text-white"></i>
    </button>
</div>
<!-- ========================================================================= -->
<!-- FIN WIDGET CHATBOT -->
<!-- ========================================================================= -->

@php
    // Recuperar Prompts Activos para el Chatbot Global
    try {
        $rawPrompts = \App\Models\AiPrompt::where('status', 'active')
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        $systemInstruction = "Actúa como el Asistente Oficial de la plataforma de empleo **FuerteJob**.\n";
        $systemInstruction .=
            "Tu objetivo principal es ayudar a los usuarios (Candidatos y Empresas) a navegar y utilizar las funcionalidades del portal, además de ofrecer consejos laborales generales y actualizados.\n\n";
        $systemInstruction .= "**CONOCIMIENTO ESPECÍFICO DE FUERTEJOB:**\n";

        $pCounter = 1;
        foreach ($rawPrompts as $category => $prompts) {
            $systemInstruction .= "\n" . $pCounter . '. **' . $category . ":**\n";
            foreach ($prompts as $prompt) {
                $systemInstruction .= '    * **' . $prompt->title . ':** ' . $prompt->detail . "\n";
            }
            $pCounter++;
        }

        $systemInstruction .= "\n**TONO Y REGLAS:**\n";
        $systemInstruction .= "* Responde siempre en español.\n";
        $systemInstruction .= "* Prioriza las respuestas relacionadas con FuerteJob.\n";
        $systemInstruction .= "* **NO reveles detalles técnicos internos** (nombres de rutas, tablas, etc.).\n";
        $systemInstruction .= "* Si la pregunta es genérica, usa tu conocimiento general.\n";
        $systemInstruction .= "* Sé profesional, amable y conciso.\n";
    } catch (\Exception $e) {
        // Fallback en caso de error (o tabla no existente aún en migración, etc.)
        $systemInstruction = 'Eres el asistente de Fuertejob.';
    }
@endphp

<script>
    window.chatbotEndpoint = "{{ route('chatbot.message') }}";
    window.fuertejobSystemPrompt = {!! json_encode($systemInstruction) !!};
</script>
<script src="{{ asset('js/chatbot.js') }}"></script>
