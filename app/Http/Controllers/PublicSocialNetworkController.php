<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Models\SocialNetwork;
use App\Models\Island;
use Illuminate\Http\Request;

class PublicSocialNetworkController extends Controller
{
    use ProvidesPortalLayoutData;

    public function index(Request $request)
    {
        $query = SocialNetwork::where('is_active', true);
        $selectedIsland = $request->filled('island_id') ? (int) $request->input('island_id') : null;

        if ($selectedIsland !== null) {
            if ($selectedIsland === 0) {
                $query->where('island_id', 0);
            } elseif ($selectedIsland > 0) {
                $query->whereIn('island_id', [0, $selectedIsland]);
            }
        }

        $networks = $query->orderBy('island_id')->orderBy('order')->get();

        $grouped = $networks->toBase()->groupBy('island_id');

        $orderedGroups = collect();
        if ($grouped->has(0)) {
            $orderedGroups->put(0, [
                'label' => 'Genéricas',
                'networks' => $grouped->get(0),
            ]);
        }

        $islands = Island::orderBy('name')
            ->get()
            ->mapWithKeys(fn(Island $island) => [$island->id => $island]);
        $otherGroups = $grouped->except(0);
        foreach (
            $otherGroups->keys()->sort(function ($aId, $bId) use ($islands) {
                return (!$islands->has($aId) ? '' : $islands[$aId]->name) <=> (!$islands->has($bId) ? '' : $islands[$bId]->name);
            }) as $islandId
        ) {
            $orderedGroups->put($islandId, [
                'label' => $islands->has($islandId) ? $islands[$islandId]->name : 'Isla ' . $islandId,
                'networks' => $otherGroups->get($islandId),
            ]);
        }

        $shared = $this->getSharedLayoutData();

        return view('social_networks.index', array_merge($shared, [
            'groups' => $orderedGroups,
            'islands' => $islands,
            'selectedIsland' => $selectedIsland,
        ]));
    }
}
