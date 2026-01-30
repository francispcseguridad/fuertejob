<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\CompanyProfile;
use App\Models\CompanyUserMembership;
use App\Models\BonoCatalog;
use App\Models\JobOffer;

class CompanyProfileController extends Controller
{
    /**
     * Muestra el dashboard principal del portal de empresas.
     */
    public function dashboard()
    {
        $profile = $this->resolveCompanyProfile();

        if (!$profile) {
            return redirect()
                ->route('empresa.profile.index')
                ->with('warning', 'Debes tener un perfil de empresa vinculado para acceder al panel.');
        }

        $ofertasvigentes = JobOffer::where('company_profile_id', $profile->id)
            ->whereIn('status', ['published', 'Publicado'])
            ->count();

        $candidatos_inscritos = JobOffer::where('company_profile_id', $profile->id)
            ->whereIn('status', ['published', 'Publicado'])
            ->withCount('candidates')
            ->get()
            ->sum('candidates_count');

        $resourceBalance = $profile->resourceBalance;
        $availableOfferCredits = (int) ($resourceBalance->available_offer_credits ?? 0);
        $availableCvViews = (int) ($resourceBalance->available_cv_views ?? 0);
        $availableUserSeats = (int) ($resourceBalance->available_user_seats ?? 0);

        return view('company.dashboard', compact(
            'ofertasvigentes',
            'candidatos_inscritos',
            'availableOfferCredits',
            'availableCvViews',
            'availableUserSeats',
        ));
    }

    /**
     * Intenta resolver el perfil de empresa del usuario autenticado.
     */
    private function resolveCompanyProfile()
    {
        $user = Auth::user();
        if (!$user) return null;

        // 1. Intentar relación directa (dueño de la empresa)
        $profile = $user->companyProfile;

        // 2. Si no es dueño, intentar vía membresía (colaborador)
        if (!$profile) {
            $membership = CompanyUserMembership::where('user_id', $user->id)
                ->with('companyProfile')
                ->latest()
                ->first();

            $profile = $membership?->companyProfile ?? null;

            if ($profile) {
                $user->setRelation('companyProfile', $profile);
            }
        }

        return $profile;
    }

    /**
     * Muestra el formulario de perfil.
     */
    public function index()
    {
        $profile = $this->resolveCompanyProfile();

        if ($profile) {
            $profile->load('sectors.parent');
        }

        return view('company.profile.index', compact('profile'));
    }

    /**
     * Almacena o ACTUALIZA el perfil de la empresa.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'legal_name' => 'required|string|max:255',
            'vat_id' => 'required|string|max:50',
            'fiscal_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $profile = $this->resolveCompanyProfile();

        if (!$profile) {
            if (Auth::user()->rol !== 'empresa') {
                return redirect()->route('home')->with('error', 'No tienes permisos para crear un perfil de empresa.');
            }
            $profile = new CompanyProfile(['user_id' => Auth::id()]);
        }

        if ($request->hasFile('logo_url')) {
            $file = $request->file('logo_url');
            $filename = Str::slug($data['company_name']) . '.' . $file->getClientOriginalExtension();
            $path = 'img/companies/' . $filename;

            $optimized = \App\Http\Controllers\HomeController::compressAndResizeImage($file, 1000, 75);
            file_put_contents(public_path($path), $optimized);
            $data['logo_url'] = $path;
        }

        $profile->fill($data)->save();

        return redirect()->route('empresa.dashboard')->with('success', 'Perfil actualizado.');
    }

    public function showBonoCatalog()
    {
        $profile = $this->resolveCompanyProfile();
        if (!$profile) {
            return redirect()->route('empresa.profile.index')->with('warning', 'Completa tu perfil.');
        }

        $bonos = BonoCatalog::where('is_active', true)->orderBy('price', 'asc')->get();
        return view('company.bono_catalog', compact('bonos', 'profile'));
    }
}
