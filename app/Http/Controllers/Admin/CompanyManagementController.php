<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailsController;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyManagementController extends Controller
{
    /**
     * Lista las empresas registradas (usuarios con rol empresa).
     */
    public function index(Request $request)
    {
        $filters = [
            'company_name' => $request->query('company_name'),
            'email' => $request->query('email'),
            'legal_name' => $request->query('legal_name'),
            'island_id' => $request->query('island_id'),
            'activo' => $request->query('activo'),
        ];

        $query = User::where('rol', 'empresa')->with('companyProfile');

        if ($filters['company_name']) {
            $query->whereHas('companyProfile', function ($q) use ($filters) {
                $q->where('company_name', 'like', '%' . $filters['company_name'] . '%');
            });
        }

        if ($filters['email']) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['legal_name']) {
            $query->whereHas('companyProfile', function ($q) use ($filters) {
                $q->where('legal_name', 'like', '%' . $filters['legal_name'] . '%');
            });
        }

        if ($filters['island_id']) {
            $query->whereHas('companyProfile', function ($q) use ($filters) {
                $q->where('island_id', $filters['island_id']);
            });
        }

        if ($filters['activo']) {
            $query->whereHas('companyProfile', function ($q) use ($filters) {
                $q->where('activo', $filters['activo']);
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($request->query('export')) {
            $exportFormat = strtolower($request->query('export'));
            return $this->handleCompanyExport($query->get(), $exportFormat, $filters);
        }

        $companies = $query->paginate(15)->withQueryString();

        return view('admin.companies', compact('companies', 'filters'));
    }

    /**
     * Muestra el formulario de gestión para una empresa específica.
     */
    public function edit(User $empresa)
    {
        $company = $empresa;
        $company->load(['companyProfile.jobOffers']);

        return view('admin.company_edit', compact('company'));
    }

    /**
     * Actualiza datos principales de la empresa.
     */
    public function update(Request $request, User $empresa)
    {
        $company = $empresa;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($company->id)],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'vat_id' => ['nullable', 'string', 'max:255'],
            'fiscal_address' => ['nullable', 'string'],
            'contact' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'activo' => ['required', 'in:1,2'],
        ]);

        DB::beginTransaction();

        try {
            $company->update($request->only('name', 'email'));

            if ($company->companyProfile) {
                $company->companyProfile->update($request->only([
                    'company_name',
                    'phone',
                    'website_url',
                    'legal_name',
                    'vat_id',
                    'fiscal_address',
                    'contact',
                    'contact_phone',
                    'contact_email',
                    'description',
                    'activo',
                ]));
            }

            if ($request->filled('email')) {
                $this->syncAssociatedUserEmail($company, $request->input('email'));
            }

            DB::commit();

            return redirect()->route('admin.empresas.edit', $company->id)
                ->with('success', 'Datos de la empresa actualizados correctamente.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocurrió un error al guardar los cambios.');
        }
    }

    /**
     * Actualiza la contraseña del usuario de empresa.
     */
    public function updatePassword(Request $request, User $empresa)
    {
        $company = $empresa;

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $company->password = Hash::make($request->password);
        $company->save();

        return redirect()->route('admin.empresas.edit', $company->id)
            ->with('success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Elimina la empresa.
     */
    public function destroy(User $empresa)
    {
        $company = $empresa;
        $company->delete();

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa eliminada correctamente.');
    }

    public function sendEmail(Request $request, User $empresa)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $senderName = Auth::user()->name ?? 'Admin FuerteJob';
        $senderEmail = Auth::user()->email ?? 'no-reply@fuertejob.com';

        try {
            MailsController::enviaremail(
                $empresa->email,
                $senderName,
                $senderEmail,
                $data['subject'],
                $data['message']
            );

            MailsController::enviaremail(
                'info@fuertejob.com',
                $senderName,
                $senderEmail,
                '[Copia] ' . $data['subject'],
                "Copia de mensaje enviado a {$empresa->email}:\n\n" . $data['message']
            );

            return response()->json(['success' => true, 'message' => 'Correo enviado.']);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'No se pudo enviar el correo.'], 500);
        }
    }

    protected function handleCompanyExport(Collection $companies, string $format, array $filters)
    {
        $rows = $companies->map(function (User $company) {
            $profile = $company->companyProfile;

            return [
                'company_name' => $profile->company_name ?? $company->name,
                'phone' => $profile->phone ?? 'N/A',
                'email' => $company->email,
                'website_url' => $profile->website_url ?? 'N/A',
                'legal_name' => $profile->legal_name ?? 'N/A',
                'vat_id' => $profile->vat_id ?? 'N/A',
                'fiscal_address' => $profile->fiscal_address ?? 'N/A',
                'contact' => $profile->contact ?? 'N/A',
                'contact_phone' => $profile->contact_phone ?? 'N/A',
                'contact_email' => $profile->contact_email ?? 'N/A',
                'island_id' => $profile->island_id ?? 'N/A',
                'activo' => $profile->activo ? 'Activo' : 'Inactivo',
            ];
        });

        $timestamp = now()->format('YmdHis');

        if ($format === 'csv') {
            $filename = "empresas-{$timestamp}.csv";
            $headers = ['Empresa', 'Teléfono', 'Email', 'Web', 'Razón Social', 'NIF', 'Dirección Fiscal', 'Contacto', 'Tel Contacto', 'Email Contacto', 'Isla', 'Activo'];

            $callback = function () use ($rows, $headers) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $headers);

                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row));
                }

                fclose($handle);
            };

            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv',
                'Cache-Control' => 'no-store, no-cache',
            ]);
        }

        if ($format === 'pdf') {
            $filename = "empresas-{$timestamp}.pdf";
            $pdf = Pdf::loadView('admin.exports.companies_pdf', [
                'companies' => $rows,
                'filters' => $filters,
            ]);

            return $pdf->download($filename);
        }

        abort(400, 'Formato de exportación no soportado.');
    }

    /**
     * Asegura que el email del usuario conectado al perfil de empresa se corresponde con el formulario.
     */
    protected function syncAssociatedUserEmail(User $company, string $newEmail): void
    {
        $profile = $company->companyProfile;

        if (!$profile) {
            return;
        }

        $associatedUser = $profile->user;

        if (!$associatedUser || $associatedUser->email === $newEmail) {
            return;
        }

        $associatedUser->update([
            'email' => $newEmail,
        ]);
    }
}
