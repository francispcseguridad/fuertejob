<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Island;
use App\Models\SocialNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialNetworkController extends Controller
{
    public function index()
    {
        $socialNetworks = SocialNetwork::with('island')->orderBy('order')->get();
        $islands = Island::orderBy('name')->get();

        return view('admin.social_networks.index', compact('socialNetworks', 'islands'));
    }

    public function store(Request $request)
    {
        $validator = $this->makeValidator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.social_networks.index')
                ->withErrors($validator)
                ->withInput()
                ->with('social_modal', 'create');
        }

        SocialNetwork::create($validator->validated());

        return redirect()
            ->route('admin.social_networks.index')
            ->with('success', 'Red social creada correctamente.');
    }

    public function update(Request $request, SocialNetwork $socialNetwork)
    {
        $validator = $this->makeValidator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.social_networks.index')
                ->withErrors($validator)
                ->withInput()
                ->with('social_modal', 'edit')
                ->with('social_modal_id', $socialNetwork->id);
        }

        $socialNetwork->update($validator->validated());

        return redirect()
            ->route('admin.social_networks.index')
            ->with('success', 'Red social actualizada correctamente.');
    }

    public function destroy(SocialNetwork $socialNetwork)
    {
        $socialNetwork->delete();

        return redirect()
            ->route('admin.social_networks.index')
            ->with('success', 'Red social eliminada correctamente.');
    }

    protected function makeValidator(Request $request)
    {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'island_id' => max(0, (int) $request->input('island_id', 0)),
        ]);

        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'icon_class' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'island_id' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
