<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PortalSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PortalSettingController extends Controller
{
    /**
     * Muestra la página de configuración visual y legal del portal.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = PortalSetting::getSettings();
        return view('admin.portal_settings', compact('settings'));
    }

    /**
     * Procesa y guarda los ajustes visuales y legales del portal.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $settings = PortalSetting::getSettings();

        $request->validate([
            'site_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'vat_id' => 'nullable|string|max:50',
            'fiscal_address' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'default_irpf' => 'required|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:10',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'imagen_academias' => 'nullable|string',
            'imagen_inmobiliarias' => 'nullable|string',
        ]);

        $data = $request->only([
            'site_name',
            'legal_name',
            'vat_id',
            'fiscal_address',
            'contact_email',
            'default_tax_rate',
            'default_irpf',
            'invoice_prefix'
        ]);

        if ($request->hasFile('logo_file')) {
            if ($settings->logo_url) {
                Storage::delete($settings->logo_url);
            }

            $file = $request->file('logo_file');
            $file->move(public_path('img/'), $file->getClientOriginalName());
            $data['logo_url'] = 'img/' . $file->getClientOriginalName();
        }

        if ($request->filled('imagen_academias')) {
            $this->deletePortalImage($settings->imagen_academias);
            $data['imagen_academias'] = $this->storePortalImage($request->input('imagen_academias'), 'academia');
        }

        if ($request->filled('imagen_inmobiliarias')) {
            $this->deletePortalImage($settings->imagen_inmobiliarias);
            $data['imagen_inmobiliarias'] = $this->storePortalImage($request->input('imagen_inmobiliarias'), 'inmobiliaria');
        }

        // 2. Guardar los datos de configuración en la única fila del modelo.
        $settings->update($data);

        return redirect()->route('admin.configuracion.index')->with('success', 'La configuración del portal ha sido actualizada exitosamente.');
    }

    private function storePortalImage(string $base64Payload, string $baseFilename): ?string
    {
        $parts = explode(';base64,', $base64Payload);
        if (count($parts) !== 2) {
            return null;
        }

        $decoded = base64_decode($parts[1]);
        if ($decoded === false) {
            return null;
        }

        $directory = public_path('img');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = "{$baseFilename}.jpg";
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $decoded);

        return 'img/' . $filename;
    }

    private function deletePortalImage(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $absolute = public_path(ltrim($relativePath, '/'));
        if (file_exists($absolute)) {
            @unlink($absolute);
        }
    }
}
