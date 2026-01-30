<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CommercialContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Models\Academia;
use App\Models\Island;
use Illuminate\Http\Request;

class PublicAcademiaController extends Controller
{
    use ProvidesPortalLayoutData;

    public function index(Request $request)
    {
        $query = Academia::with('island');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('island_id')) {
            $query->where('island_id', $request->input('island_id'));
        }

        $academias = $query->orderBy('name')->paginate(12)->withQueryString();
        $islands = Island::orderBy('name')->get();
        $commercialContactCaptchaQuestion = CommercialContactController::generateCaptchaQuestion();

        $shared = $this->getSharedLayoutData();
        return view('academias.index', array_merge($shared, compact('academias', 'islands', 'commercialContactCaptchaQuestion')));
    }
}
