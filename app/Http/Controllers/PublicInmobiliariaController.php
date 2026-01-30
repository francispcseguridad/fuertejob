<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CommercialContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Models\Inmobiliaria;
use App\Models\Island;
use Illuminate\Http\Request;

class PublicInmobiliariaController extends Controller
{
    use ProvidesPortalLayoutData;

    public function index(Request $request)
    {
        $query = Inmobiliaria::with('island');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('island_id')) {
            $query->where('island_id', $request->input('island_id'));
        }

        $inmobiliarias = $query->orderBy('name')->paginate(12)->withQueryString();
        $islands = Island::orderBy('name')->get();
        $commercialContactCaptchaQuestion = CommercialContactController::generateCaptchaQuestion();

        $shared = $this->getSharedLayoutData();
        return view('inmobiliarias.index', array_merge($shared, compact('inmobiliarias', 'islands', 'commercialContactCaptchaQuestion')));
    }
}
