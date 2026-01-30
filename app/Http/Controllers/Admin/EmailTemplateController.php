<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Importar el Controller base
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class EmailTemplateController extends Controller
{
    /**
     * Muestra la lista de plantillas y la vista de administración principal.
     */
    public function index()
    {
        // Obtener todas las plantillas para listarlas en la vista
        $templates = EmailTemplate::all();

        // Retornar la vista de administración con los datos
        return view('admin.email-templates', compact('templates'));
    }

    /**
     * Almacena una nueva plantilla en la base de datos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name',
            'type' => 'required|string|max:255|unique:email_templates,type',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        EmailTemplate::create($request->all());

        return Redirect::route('admin.email-templates.index')->with('success', 'Plantilla creada exitosamente.');
    }

    /**
     * Actualiza una plantilla existente.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'type' => 'required|string|max:255|unique:email_templates,type,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $emailTemplate->update($request->all());

        return Redirect::route('admin.email-templates.index')->with('success', 'Plantilla actualizada exitosamente.');
    }

    /**
     * Elimina una plantilla específica.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return Redirect::route('admin.email-templates.index')->with('success', 'Plantilla eliminada correctamente.');
    }
}
