<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeCompanyCta;
use Illuminate\Http\Request;

class HomeCompanyCtaController extends Controller
{
    public function index()
    {
        $ctas = HomeCompanyCta::all();
        return view('admin.home_company_ctas.index', compact('ctas'));
    }
    public function create()
    {
        return view('admin.home_company_ctas.create');
    }
    public function store(Request $request)
    {
        $request->validate(['title' => 'required']);
        $request->merge(['is_active' => $request->has('is_active')]);
        HomeCompanyCta::create($request->all());
        return redirect()->route('admin.home_company_ctas.index')->with('success', 'Guardado.');
    }
    public function edit(HomeCompanyCta $homeCompanyCta)
    {
        return view('admin.home_company_ctas.edit', compact('homeCompanyCta'));
    }
    public function update(Request $request, HomeCompanyCta $homeCompanyCta)
    {
        $request->validate(['title' => 'required']);
        $request->merge(['is_active' => $request->has('is_active')]);
        $homeCompanyCta->update($request->all());
        return redirect()->route('admin.home_company_ctas.index')->with('success', 'Actualizado.');
    }
    public function destroy(HomeCompanyCta $homeCompanyCta)
    {
        $homeCompanyCta->delete();
        return back()->with('success', 'Eliminado.');
    }
}
