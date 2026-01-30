<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Menu;
use App\Models\SocialNetwork;
use App\Models\PortalSetting;

trait ProvidesPortalLayoutData
{
    protected function getSharedLayoutData(): array
    {
        $menus = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'primary')
            ->orderBy('order')
            ->get();

        $footer1 = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'footer_1')
            ->orderBy('order')
            ->get();

        $footer2 = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'footer_2')
            ->orderBy('order')
            ->get();

        $footer3 = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'footer_3')
            ->orderBy('order')
            ->get();

        $socialNetworks = SocialNetwork::where('is_active', true)
            ->where('island_id', 0)
            ->orderBy('order')
            ->get();

        $portalSettings = PortalSetting::getSettings();

        return compact('menus', 'footer1', 'footer2', 'footer3', 'socialNetworks', 'portalSettings');
    }
}
