<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para la administración de las Preguntas Frecuentes (FAQ) y artículos Wiki.
 * Maneja el CRUD para el modelo FaqItem.
 */
class FaqItemController extends Controller
{
    /**
     * Muestra una lista de todos los items FAQ, ordenados por público objetivo y orden de clasificación.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Obtiene todos los items FAQ, ordenados por público objetivo y luego por 'sort_order'.
        $faqItems = FaqItem::orderBy('target_audience')
            ->orderBy('sort_order')
            ->paginate(20);

        // Retorna la vista con los datos.
        // Asumiendo que la vista se encuentra en 'admin.faq_items.index'
        return view('admin.faq_items.index', compact('faqItems'));
    }

    /**
     * Muestra el formulario para crear un nuevo item FAQ.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $audiences = [
            FaqItem::AUDIENCE_WORKER => 'Trabajador',
            FaqItem::AUDIENCE_COMPANY => 'Empresa',
            FaqItem::AUDIENCE_GENERAL => 'General',
        ];

        // Asumiendo que la vista se encuentra en 'admin.faq_items.create'
        return view('admin.faq_items.create', compact('audiences'));
    }

    /**
     * Almacena un nuevo item FAQ en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Reglas de validación
        $validatedData = $request->validate([
            'target_audience' => ['required', Rule::in([FaqItem::AUDIENCE_WORKER, FaqItem::AUDIENCE_COMPANY, FaqItem::AUDIENCE_GENERAL])],
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        try {
            // 2. Creación del registro
            FaqItem::create($validatedData);

            // 3. Redirección con mensaje de éxito
            return redirect()->route('admin.faq_items.index')
                ->with('success', 'Item FAQ/Wiki creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear FaqItem: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Hubo un error al crear el item. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra el formulario para editar un item FAQ existente.
     *
     * @param  \App\Models\FaqItem  $faqItem
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(FaqItem $faqItem)
    {
        $audiences = [
            FaqItem::AUDIENCE_WORKER => 'Trabajador',
            FaqItem::AUDIENCE_COMPANY => 'Empresa',
            FaqItem::AUDIENCE_GENERAL => 'General',
        ];

        // Asumiendo que la vista se encuentra en 'admin.faq_items.edit'
        return view('admin.faq_items.edit', compact('faqItem', 'audiences'));
    }

    /**
     * Actualiza un item FAQ existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FaqItem  $faqItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, FaqItem $faqItem)
    {
        // 1. Reglas de validación
        $validatedData = $request->validate([
            'target_audience' => ['required', Rule::in([FaqItem::AUDIENCE_WORKER, FaqItem::AUDIENCE_COMPANY, FaqItem::AUDIENCE_GENERAL])],
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            // El checkbox de 'is_published' podría no estar presente, así que lo manejamos después
            'is_published' => 'nullable|boolean',
        ]);

        try {
            // Asegurarse de que is_published se establezca correctamente si no está presente en la solicitud (ej. si es un checkbox desmarcado)
            $validatedData['is_published'] = $request->has('is_published');

            // 2. Actualización del registro
            $faqItem->update($validatedData);

            // 3. Redirección con mensaje de éxito
            return redirect()->route('admin.faq_items.index')
                ->with('success', 'Item FAQ/Wiki actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar FaqItem ID: ' . $faqItem->id . ' - ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Hubo un error al actualizar el item. Inténtalo de nuevo.');
        }
    }

    /**
     * Elimina un item FAQ de la base de datos.
     *
     * @param  \App\Models\FaqItem  $faqItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FaqItem $faqItem)
    {
        try {
            $faqItem->delete();

            // Redirección con mensaje de éxito
            return redirect()->route('admin.faq_items.index')
                ->with('success', 'Item FAQ/Wiki eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar FaqItem ID: ' . $faqItem->id . ' - ' . $e->getMessage());
            return back()
                ->with('error', 'Hubo un error al eliminar el item. Inténtalo de nuevo.');
        }
    }
}
