<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();
        $parents = Menu::orderBy('title')->get();
        return view('admin.menus.index', compact('menus', 'parents'));
    }

    public function create()
    {
        $parents = Menu::orderBy('title')->get();
        return view('admin.menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'location' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Menú creado correctamente.',
                'menu' => $menu,
            ]);
        }

        return redirect()->route('admin.menus.index')->with('success', 'Menú creado correctamente.');
    }

    public function edit(Menu $menu)
    {
        $parents = Menu::where('id', '!=', $menu->id)->orderBy('title')->get();
        return view('admin.menus.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'location' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($request->parent_id == $menu->id) {
            return back()->withErrors(['parent_id' => 'El menú no puede ser padre de sí mismo.']);
        }

        $menu->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Menú actualizado correctamente.',
                'menu' => $menu,
            ]);
        }

        return redirect()->route('admin.menus.index')->with('success', 'Menú actualizado correctamente.');
    }

    public function destroy(Request $request, Menu $menu)
    {
        $menu->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Menú eliminado correctamente.'
            ]);
        }

        return redirect()->route('admin.menus.index')->with('success', 'Menú eliminado correctamente.');
    }
}
