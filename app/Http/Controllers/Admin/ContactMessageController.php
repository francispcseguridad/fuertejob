<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MailsController;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactMessageController extends Controller
{
    use ProvidesPortalLayoutData;
    public const ROLE_TYPES = [
        'visitor' => 'Visitante',
        'company' => 'Empresa',
        'worker' => 'Trabajador',
    ];

    public const INQUIRY_TYPES = [
        'platform' => 'Manejo de la plataforma',
        'support' => 'Soporte',
        'administration' => 'Administración',
    ];

    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($name = $request->input('name')) {
            $query->where(function ($sub) use ($name) {
                $sub->where('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        if ($role = $request->input('role_type')) {
            $query->where('role_type', $role);
        }

        if ($type = $request->input('inquiry_type')) {
            $query->where('inquiry_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.contact_messages.index', array_merge($this->getSharedLayoutData(), [
            'messages' => $messages,
            'roleTypes' => self::ROLE_TYPES,
            'inquiryTypes' => self::INQUIRY_TYPES,
            'filters' => $request->only(['name', 'role_type', 'inquiry_type', 'status', 'date_from', 'date_to']),
        ]));
    }

    public function show(ContactMessage $contactMessage)
    {
        return view('admin.contact_messages.show', array_merge($this->getSharedLayoutData(), [
            'contactMessage' => $contactMessage,
            'roleTypes' => self::ROLE_TYPES,
            'inquiryTypes' => self::INQUIRY_TYPES,
        ]));
    }

    public function respond(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'response_message' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:new,responded,closed'],
        ]);

        $messageBody = trim($data['response_message']);

        try {
            MailsController::enviaremail(
                $contactMessage->email,
                trim($contactMessage->first_name . ' ' . $contactMessage->last_name) ?: 'Contacto',
                'no-reply@fuertejob.com',
                'Respuesta a tu consulta en FuerteJob',
                $messageBody
            );

            $contactMessage->update([
                'response_message' => $messageBody,
                'responded_at' => now(),
                'status' => $data['status'],
            ]);

            return redirect()->route('admin.contact_messages.show', $contactMessage)
                ->with('success_response', 'Correo enviado y estado actualizado.');
        } catch (\Exception $e) {
            Log::error('Error al responder contacto: ' . $e->getMessage(), ['id' => $contactMessage->id]);
            return back()->withErrors(['response_message' => 'No se pudo enviar el correo. Intenta de nuevo.']);
        }
    }

    public function print(ContactMessage $contactMessage)
    {
        return view('admin.contact_messages.print', array_merge($this->getSharedLayoutData(), [
            'contactMessage' => $contactMessage,
            'roleTypes' => self::ROLE_TYPES,
            'inquiryTypes' => self::INQUIRY_TYPES,
        ]));
    }
}
