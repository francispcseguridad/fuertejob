<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Modelo principal de usuario
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Cv;
use App\Http\Controllers\MailsController;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class WorkerManagementController extends Controller
{
    /**
     * Muestra una lista de solicitantes (usuarios con rol 'candidato').
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = [
            'name' => $request->query('name'),
            'email' => $request->query('email'),
            'city' => $request->query('city'),
            'country' => $request->query('country'),
        ];

        $query = User::where('rol', 'trabajador')->with('workerProfile');

        if ($filters['name']) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if ($filters['email']) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['city']) {
            $query->whereHas('workerProfile', function ($sub) use ($filters) {
                $sub->where('city', 'like', '%' . $filters['city'] . '%');
            });
        }

        if ($filters['country']) {
            $query->whereHas('workerProfile', function ($sub) use ($filters) {
                $sub->where('country', 'like', '%' . $filters['country'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($request->query('export')) {
            $exportFormat = strtolower($request->query('export'));
            return $this->handleWorkerExport(
                $query->get(),
                $exportFormat,
                $filters
            );
        }

        $workers = $query->paginate(15)->withQueryString();

        return view('admin.workers', compact('workers', 'filters'));
    }

    /**
     * Maneja la lógica de exportar la lista de trabajadores a CSV o PDF.
     *
     * @param \Illuminate\Support\Collection $workers
     * @param string $format
     * @param array $filters
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    protected function handleWorkerExport(Collection $workers, string $format, array $filters)
    {
        $rows = $workers->map(function (User $worker) {
            $profile = $worker->workerProfile;

            return [
                'id' => $worker->id,
                'name' => $worker->name,
                'email' => $worker->email,
                'city' => $profile->city ?? 'N/A',
                'country' => $profile->country ?? 'N/A',
                'status' => $worker->email_verified_at ? 'Verificado' : 'Pendiente',
            ];
        });

        $timestamp = now()->format('YmdHis');

        if ($format === 'csv') {
            $filename = "trabajadores-{$timestamp}.csv";
            $headers = ['ID', 'Nombre', 'Email', 'Ciudad', 'País', 'Estado'];

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
            $filename = "trabajadores-{$timestamp}.pdf";
            $pdf = Pdf::loadView('admin.exports.workers_pdf', [
                'workers' => $rows,
                'filters' => $filters,
            ]);

            return $pdf->download($filename);
        }

        abort(400, 'Formato de exportación no soportado.');
    }

    /**
     * Muestra el formulario para editar un solicitante específico.
     *
     * @param  \App\Models\User  $worker
     * @return \Illuminate\Http\Response
     */
    public function edit(User $candidato)
    {
        $worker = $candidato;
        $worker->load(['workerProfile.educations', 'workerProfile.experiences']);

        $primaryCv = null;
        $workerProfileId = optional($worker->workerProfile)->id;

        if ($workerProfileId) {
            $primaryCv = Cv::getPrimaryCvByWorkerProfileId($workerProfileId);

            if (!$primaryCv) {
                $primaryCv = Cv::where('worker_profile_id', $workerProfileId)
                    ->orderByDesc('created_at')
                    ->first();
            }
        }

        return view('admin.worker_edit', compact('worker', 'primaryCv'));
    }

    /**
     * Actualiza los datos personales de un solicitante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $worker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $candidato)
    {
        $worker = $candidato;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($worker->id)],

            // Validación para campos de perfil (si se actualizan directamente desde el admin)
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            // ... otros campos del perfil ...
        ]);

        DB::beginTransaction();

        try {
            // Actualizar datos del Usuario
            $worker->update($request->only('name', 'email'));

            // Actualizar datos del Perfil del Trabajador si existe
            if ($worker->profile) {
                $worker->profile->update($request->only([
                    'first_name',
                    'last_name',
                    // ... otros campos del perfil ...
                ]));
            }

            DB::commit();

            return redirect()->route('admin.candidatos.edit', $worker->id)
                ->with('success', 'Datos del solicitante actualizados correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar los datos.');
        }
    }

    /**
     * Actualiza la contraseña de un solicitante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $worker
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $solicitante)
    {
        $worker = $solicitante;

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $worker->password = Hash::make($request->password);
        $worker->save();

        return redirect()->route('admin.candidatos.edit', $worker->id)
            ->with('success', 'Contraseña restablecida correctamente.');
    }

    /**
     * Elimina un solicitante del sistema.
     *
     * @param  \App\Models\User  $worker
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $candidato)
    {
        $worker = $candidato;
        // Se asume que las claves foráneas en la base de datos están configuradas con ON DELETE CASCADE
        // para que profile, experiences, educations se eliminen automáticamente.
        $worker->delete();

        return redirect()->route('admin.candidatos.index')
            ->with('success', 'Solicitante eliminado correctamente.');
    }

    /**
     * Enviar un correo al candidato desde el panel admin y copiar a info@fuertejob.com.
     */
    public function sendEmail(Request $request, User $candidato)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $senderName = Auth::user()->name ?? 'Admin FuerteJob';
        $senderEmail = Auth::user()->email ?? 'no-reply@fuertejob.com';

        try {
            MailsController::enviaremail(
                $candidato->email,
                $senderName,
                $senderEmail,
                $data['subject'],
                $data['message']
            );

            // Copia a info
            MailsController::enviaremail(
                'info@fuertejob.com',
                $senderName,
                $senderEmail,
                '[Copia] ' . $data['subject'],
                "Copia de mensaje enviado a {$candidato->email}:\n\n" . $data['message']
            );

            return response()->json(['success' => true, 'message' => 'Mensaje enviado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo enviar el correo.'], 500);
        }
    }
}
