<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyResourceBalance;
use App\Models\CompanyUserMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyUserController extends Controller
{
    /**
     * Muestra el listado de usuarios corporativos junto al saldo de asientos.
     */
    public function index(Request $request)
    {
        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile) {
            return redirect()
                ->route('empresa.profile.index')
                ->with('warning', 'Completa tu perfil de empresa antes de acceder a esta sección.');
        }

        $resourceBalance = CompanyResourceBalance::firstOrCreate(
            ['company_profile_id' => $companyProfile->id],
            [
                'available_cv_views' => 0,
                'available_user_seats' => 0,
            ]
        );

        $memberships = CompanyUserMembership::with('user')
            ->where('company_profile_id', $companyProfile->id)
            ->latest()
            ->paginate(10);

        return view('company.users.index', [
            'resourceBalance' => $resourceBalance,
            'memberships' => $memberships,
        ]);
    }

    /**
     * Crea un usuario vinculado a la empresa si hay asientos disponibles.
     */
    public function store(Request $request)
    {
        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile) {
            return redirect()
                ->route('empresa.profile.index')
                ->with('warning', 'Completa tu perfil de empresa antes de acceder a esta sección.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $balance = CompanyResourceBalance::firstOrCreate(
            ['company_profile_id' => $companyProfile->id],
            [
                'available_cv_views' => 0,
                'available_user_seats' => 0,
            ]
        );

        if ((int) $balance->available_user_seats <= 0) {
            return back()
                ->withInput()
                ->with('error', 'No hay asientos disponibles. Compra un bono para añadir nuevos usuarios.');
        }

        try {
            $user = DB::transaction(function () use ($validated, $companyProfile) {
                $lockedBalance = CompanyResourceBalance::where('company_profile_id', $companyProfile->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedBalance || (int) $lockedBalance->available_user_seats <= 0) {
                    throw new \RuntimeException('No hay asientos disponibles para asignar.');
                }

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'rol' => 'empresa_colaborador',
                ]);

                $membership = CompanyUserMembership::create([
                    'company_profile_id' => $companyProfile->id,
                    'user_id' => $user->id,
                    'bono_purchase_id' => null,
                ]);

                if (!$membership) {
                    throw new \RuntimeException('No se pudo asignar el asiento de usuario.');
                }

                return $user;
            });
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage() ?: 'No se pudo crear el usuario.');
        }

        optional($user)->sendEmailVerificationNotification();

        return redirect()
            ->route('empresa.usuarios.index')
            ->with('success', 'Usuario creado y asiento asignado correctamente.');
    }
}
