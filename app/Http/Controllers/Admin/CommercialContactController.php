<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Http\Controllers\Controller;
use App\Models\CommercialContact;
use Illuminate\Http\Request;

class CommercialContactController extends Controller
{
    use ProvidesPortalLayoutData;

    public function index(Request $request)
    {
        $query = CommercialContact::query();

        if ($origin = $request->input('origin')) {
            $query->where('origin', $origin);
        }

        if ($readState = $request->input('is_read')) {
            if ($readState === 'read') {
                $query->where('is_read', true);
            } elseif ($readState === 'unread') {
                $query->where('is_read', false);
            }
        }

        $contacts = $query->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $origins = CommercialContact::select('origin')
            ->distinct()
            ->orderBy('origin')
            ->pluck('origin');

        return view('admin.commercial_contacts.index', array_merge($this->getSharedLayoutData(), [
            'contacts' => $contacts,
            'origins' => $origins,
            'filters' => $request->only(['origin', 'is_read']),
        ]));
    }

    public function show(Request $request, CommercialContact $contacto_comercial)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'contact' => $this->contactPayload($contacto_comercial),
            ]);
        }

        return view('admin.commercial_contacts.show', array_merge($this->getSharedLayoutData(), [
            'contact' => $contacto_comercial,
        ]));
    }

    public function update(Request $request, CommercialContact $contacto_comercial)
    {
        $data = $request->validate([
            'is_read' => ['required', 'boolean'],
        ]);

        $contacto_comercial->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Estado actualizado correctamente.',
                'contact' => $this->contactPayload($contacto_comercial->refresh()),
            ]);
        }

        return redirect()->route('admin.contactos-comerciales.show', $contacto_comercial)
            ->with('success', 'Estado actualizado correctamente.');
    }

    private function contactPayload(CommercialContact $contact): array
    {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'origin' => $contact->origin,
            'detail' => $contact->detail,
            'ip_address' => $contact->ip_address,
            'is_read' => (bool) $contact->is_read,
            'created_at_formatted' => optional($contact->created_at)->format('d/m/Y H:i'),
        ];
    }
}
