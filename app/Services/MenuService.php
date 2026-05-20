<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;

class MenuService
{
    public function sidebarMenus(User $user): Collection
    {
        $menus = Menu::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get();

        return $menus
            ->map(fn (Menu $m) => $this->filterBranch($m, $user))
            ->filter()
            ->values();
    }

    private function filterBranch(Menu $menu, User $user): ?array
    {
        if (! $this->userCanSeeMenu($menu, $user)) {
            return null;
        }

        $children = $menu->relationLoaded('children') ? $menu->children : collect();
        $kids = $children
            ->map(fn (Menu $c) => $this->filterBranch($c, $user))
            ->filter()
            ->values()
            ->all();

        if ($menu->children->isNotEmpty() && count($kids) === 0) {
            return null;
        }

        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'route' => $menu->route_name ? route($menu->route_name) : '#',
            'icon' => $menu->icon ?? 'fa-circle',
            'children' => $kids,
        ];
    }

    private function userCanSeeMenu(Menu $menu, User $user): bool
    {
        if ($menu->permission_slug && ! $user->hasPermission($menu->permission_slug)) {
            return false;
        }

        if ($menu->module_key && ! $user->isSuperAdmin()) {
            $inst = $user->institution;
            if (! $inst) {
                return false;
            }

            if (! $inst->moduleEnabled($menu->module_key)) {
                return false;
            }
        }

        return true;
    }
}
