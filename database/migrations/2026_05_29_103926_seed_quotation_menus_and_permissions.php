<?php

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Permissions
        $permissions = [
            ['name' => 'Manage Quotations', 'slug' => 'quotations.manage', 'module' => 'invoices'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign to Super Admin role
        $role = \App\Models\Role::where('slug', 'super-admin')->first();
        if ($role) {
            $perms = Permission::whereIn('slug', collect($permissions)->pluck('slug'))->pluck('id');
            $role->permissions()->syncWithoutDetaching($perms);
        }

        // 2. Create Menu
        $parent = Menu::where('name', 'Invoice Mgmt')->first();
        if ($parent) {
            Menu::create([
                'parent_id' => $parent->id,
                'name' => 'Quotations',
                'route_name' => 'admin.quotations.index',
                'icon' => 'fa-file-signature',
                'permission_slug' => 'quotations.manage',
                'sort_order' => 2, // Put it before Invoices (which is 3)
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Remove Menu
        Menu::where('name', 'Quotations')->delete();

        // Remove Permissions
        $slugs = ['quotations.manage'];
        Permission::whereIn('slug', $slugs)->delete();
    }
};
