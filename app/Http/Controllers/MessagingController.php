<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MessagingController extends Controller
{
    /**
     * Muestra la bandeja de entrada del usuario autenticado (lista de hilos).
     */
    public function index()
    {
        $userId = Auth::id();

        // Obtener todos los hilos donde el usuario es el starter O el receiver.
        // Ordenamos por la marca de tiempo del último mensaje.
        $threads = Thread::where('starter_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with([
                'starter.workerProfile',
                'starter.companyProfile',
                'receiver.workerProfile',
                'receiver.companyProfile',
                'messages'
            ]) // Precarga de relaciones para ambos roles
            ->orderByDesc('last_message_at')
            ->get();

        // Transformar la colección para la vista (Interlocutor, Foto, Nombres, Counts)
        $threads = $threads->map(function ($thread) use ($userId) {
            // Contar mensajes no leídos
            $unreadCount = $thread->messages
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            // Identificar al interlocutor (usando comparación débil '==' por si hay strings vs ints)
            $isStarter = $thread->starter_id == $userId;
            $otherUser = $isStarter ? $thread->receiver : $thread->starter;

            // Datos por defecto para usuario desconocido o eliminado
            $interlocutor = [
                'id' => 0,
                'name' => 'Usuario Desconocido',
                'role_label' => 'Desconocido',
                'role' => 'unknown',
            ];

            if ($otherUser) {
                $name = $otherUser->name;
                $roleLabel = 'Usuario';

                if ($otherUser->hasCompanyRole() && $otherUser->companyProfile) {
                    $name = $otherUser->companyProfile->company_name ?? $otherUser->name;
                    $roleLabel = 'Empresa';
                } elseif ($otherUser->rol === 'trabajador' && $otherUser->workerProfile) {
                    $name = $otherUser->workerProfile->first_name . ' ' . $otherUser->workerProfile->last_name;
                    $roleLabel = 'Candidato';
                }

                $interlocutor = [
                    'id' => $otherUser->id,
                    'name' => $name,
                    'role_label' => $roleLabel,
                    'role' => $otherUser->rol,
                ];
            } else {
                // Fallback para usuario desconocido (si no entra en el if anterior, aunque mi lógica actual no tiene else explícito aquí porque inicialicé arriba, voy a limpiar esto para que coincida con la solicitud de simplificación)
                // Re-hago la lógica completa del bloque para ser más limpio.
            }

            // Convertir hilo a array y adjuntar datos calculados
            $threadData = $thread->toArray();
            $threadData['interlocutor'] = $interlocutor;
            $threadData['formatted_date'] = $thread->last_message_at ? \Carbon\Carbon::parse($thread->last_message_at)->diffForHumans() : '';
            $threadData['unread_count'] = $unreadCount;

            return $threadData;
        })->values(); // Resetear llaves para asegurar array JSON


        return view('messaging.inbox', compact('threads'));
    }

    /**
     * Muestra un hilo de mensajes específico y marca los mensajes como leídos.
     */
    /**
     * Muestra un hilo de mensajes específico y marca los mensajes como leídos.
     */
    public function show(Thread $thread)
    {
        $userId = Auth::id();

        // 1. Verificar si el usuario es participante del hilo
        if ($thread->starter_id !== $userId && $thread->receiver_id !== $userId) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes acceso a este hilo de conversación.');
        }

        // 2. Marcar todos los mensajes del otro participante como leídos
        $thread->messages()
            ->where('sender_id', '!=', $userId) // Mensajes que no envié yo
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        // 3. Cargar el hilo con todos los mensajes y la información del remitente
        $thread->load(['messages.sender', 'starter.workerProfile', 'starter.companyProfile', 'receiver.workerProfile', 'receiver.companyProfile']);

        if (request()->wantsJson()) {
            // Preparar datos del interlocutor para el modal JSON
            $otherUser = $thread->starter_id == $userId ? $thread->receiver : $thread->starter;

            $name = $otherUser->name;
            $roleLabel = 'Usuario';

            if ($otherUser && $otherUser->hasCompanyRole() && $otherUser->companyProfile) {
                $name = $otherUser->companyProfile->company_name ?? $otherUser->name;
                $roleLabel = 'Empresa';
            } elseif ($otherUser && $otherUser->rol === 'trabajador' && $otherUser->workerProfile) {
                $name = $otherUser->workerProfile->first_name . ' ' . $otherUser->workerProfile->last_name;
                $roleLabel = 'Candidato';
            }

            $thread->interlocutor = [
                'name' => $name,
                'role_label' => $roleLabel,
            ];

            return response()->json($thread);
        }

        return view('messaging.show', compact('thread'));
    }

    /**
     * (AJAX) Envía un nuevo mensaje a un hilo existente.
     */
    public function sendMessage(Request $request, Thread $thread)
    {
        $userId = Auth::id();

        // 1. Verificar si el usuario es participante del hilo
        if ($thread->starter_id !== $userId && $thread->receiver_id !== $userId) {
            return response()->json(['error' => 'No autorizado para enviar mensajes a este hilo.'], 403);
        }

        // 2. Validación
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // 3. Creación del mensaje
        $message = $thread->messages()->create([
            'sender_id' => $userId,
            'content' => $request->content,
            'read_at' => null, // Siempre se envía como no leído por el receptor
        ]);

        // 4. Actualizar la marca de tiempo del último mensaje en el hilo
        $thread->update(['last_message_at' => Carbon::now()]);

        // Enviar notificación por email
        $currentUser = Auth::user();
        $recipientUser = ($thread->starter_id === $userId) ? $thread->receiver : $thread->starter;

        if ($recipientUser) {
            $emailButtonUrl = route('messaging.inbox');
            $emailMessage = "Tienes un nuevo mensaje en FuerteJob.<br><br>" .
                "<div style='text-align: center; margin-top: 20px;'>" .
                "<a href='{$emailButtonUrl}' style='display: inline-block; padding: 12px 24px; font-size: 16px; color: #ffffff; background-color: #4F46E5; border-radius: 6px; text-decoration: none;'>Ir a la Bandeja de Entrada</a>" .
                "</div>";

            \App\Http\Controllers\MailsController::enviaremail(
                $recipientUser->email,
                $currentUser->name,
                $currentUser->email, // Email de contacto (del remitente)
                'Tienes un nuevo mensaje en FuerteJob',
                $emailMessage
            );
        }

        // 5. Devolver el mensaje creado (para renderizarlo en el chat)
        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
            'formatted_time' => $message->created_at->format('H:i'), // Formato simple para la vista
        ]);
    }

    /**
     * (Función de ayuda) Inicia o recupera un hilo de conversación.
     * Esto se usaría, por ejemplo, al pulsar "Contactar Candidato" o "Contactar Admin".
     * * @param int $recipientId ID del usuario al que se quiere contactar
     * @param string $resourceType Tipo de recurso asociado (e.g., App\Models\JobOffer)
     * @param int $resourceId ID del recurso
     */
    public function startThread($recipientId, $resourceType, $resourceId)
    {
        $starterId = Auth::id();

        // El orden de starter_id y receiver_id debe ser consistente para encontrar el hilo.
        // Usamos un orden canónico: el ID más pequeño como starter_id y el más grande como receiver_id.
        $participant1 = min($starterId, $recipientId);
        $participant2 = max($starterId, $recipientId);

        // Buscar el hilo existente
        $thread = Thread::where(function ($query) use ($participant1, $participant2) {
            $query->where('starter_id', $participant1)
                ->where('receiver_id', $participant2);
        })
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->first();

        // Si no existe, crear uno nuevo
        if (!$thread) {
            $thread = Thread::create([
                'starter_id' => $participant1,
                'receiver_id' => $participant2,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'last_message_at' => Carbon::now(),
            ]);
        }

        // Redirigir a la vista del chat
        return redirect()->route('messaging.show', $thread);
    }

    public function startThreadAjax(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'resource_type' => 'required|string',
            'resource_id' => 'required|integer',
        ]);

        $starterId = Auth::id();
        $recipientId = (int) $data['recipient_id'];
        $participant1 = min($starterId, $recipientId);
        $participant2 = max($starterId, $recipientId);

        $thread = Thread::where(function ($query) use ($participant1, $participant2) {
            $query->where('starter_id', $participant1)
                ->where('receiver_id', $participant2);
        })
            ->where('resource_type', $data['resource_type'])
            ->where('resource_id', $data['resource_id'])
            ->first();

        if (!$thread) {
            $thread = Thread::create([
                'starter_id' => $participant1,
                'receiver_id' => $participant2,
                'resource_type' => $data['resource_type'],
                'resource_id' => $data['resource_id'],
                'last_message_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * (AJAX) Envía un mensaje a un candidato desde la vista de selección.
     */
    public function contactCandidateFromSelection(Request $request)
    {
        $request->validate([
            'selection_id' => 'required|exists:candidate_selections,id',
            'message' => 'required|string|max:1000',
        ]);

        $selection = \App\Models\CandidateSelection::with(['workerProfile.user', 'jobOffer'])->findOrFail($request->selection_id);
        $currentUser = Auth::user();
        $recipientUser = $selection->workerProfile->user;
        $jobOffer = $selection->jobOffer;

        // Lógica de hilo (similar a startThread pero sin redirección y con creación de mensaje)
        $starterId = $currentUser->id;
        $recipientId = $recipientUser->id;
        $participant1 = min($starterId, $recipientId);
        $participant2 = max($starterId, $recipientId);

        $thread = Thread::where(function ($query) use ($participant1, $participant2) {
            $query->where('starter_id', $participant1)
                ->where('receiver_id', $participant2);
        })
            ->where('resource_type', \App\Models\JobOffer::class)
            ->where('resource_id', $jobOffer->id)
            ->first();

        if (!$thread) {
            $thread = Thread::create([
                'starter_id' => $participant1,
                'receiver_id' => $participant2,
                'resource_type' => \App\Models\JobOffer::class,
                'resource_id' => $jobOffer->id,
                'last_message_at' => Carbon::now(),
            ]);
        }

        // Crear el mensaje
        $thread->messages()->create([
            'sender_id' => $currentUser->id,
            'content' => $request->message,
            'read_at' => null,
        ]);

        $thread->update(['last_message_at' => Carbon::now()]);

        // Enviar notificación por email
        $emailButtonUrl = route('messaging.inbox');
        $emailMessage = "Tienes un nuevo mensaje en FuerteJob.<br><br>" .
            "<div style='text-align: center; margin-top: 20px;'>" .
            "<a href='{$emailButtonUrl}' style='display: inline-block; padding: 12px 24px; font-size: 16px; color: #ffffff; background-color: #4F46E5; border-radius: 6px; text-decoration: none;'>Ir a la Bandeja de Entrada</a>" .
            "</div>";

        \App\Http\Controllers\MailsController::enviaremail(
            $recipientUser->email,
            $currentUser->name,
            $currentUser->email, // Email de contacto (del remitente)
            'Tienes un nuevo mensaje en FuerteJob',
            $emailMessage
        );

        return response()->json(['success' => true, 'message' => 'Mensaje enviado correctamente al candidato.']);
    }
}
