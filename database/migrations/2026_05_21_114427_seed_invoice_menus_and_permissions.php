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
            ['name' => 'Manage Customers', 'slug' => 'customers.manage', 'module' => 'invoices'],
            ['name' => 'Manage Suppliers', 'slug' => 'suppliers.manage', 'module' => 'invoices'],
            ['name' => 'Manage Invoices', 'slug' => 'invoices.manage', 'module' => 'invoices'],
            ['name' => 'View Invoice Reports', 'slug' => 'invoices.reports', 'module' => 'invoices'],
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

        // 2. Create Menus
        $parent = Menu::create([
            'name' => 'Invoice Mgmt',
            'icon' => 'fa-file-invoice-dollar',
            'sort_order' => 50,
            'is_active' => true,
        ]);

        Menu::create([
            'parent_id' => $parent->id,
            'name' => 'Customers',
            'route_name' => 'admin.customers.index',
            'icon' => 'fa-users',
            'permission_slug' => 'customers.manage',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'parent_id' => $parent->id,
            'name' => 'Suppliers',
            'route_name' => 'admin.suppliers.index',
            'icon' => 'fa-truck-field',
            'permission_slug' => 'suppliers.manage',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'parent_id' => $parent->id,
            'name' => 'Invoices',
            'route_name' => 'admin.invoices.index',
            'icon' => 'fa-file-invoice',
            'permission_slug' => 'invoices.manage',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Menu::create([
            'parent_id' => $parent->id,
            'name' => 'Reports',
            'route_name' => 'admin.invoice-reports.index',
            'icon' => 'fa-chart-pie',
            'permission_slug' => 'invoices.reports',
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        // Remove Menus
        $parent = Menu::where('name', 'Invoice Mgmt')->first();
        if ($parent) {
            Menu::where('parent_id', $parent->id)->delete();
            $parent->delete();
        }

        // Remove Permissions
        $slugs = ['customers.manage', 'suppliers.manage', 'invoices.manage', 'invoices.reports'];
        Permission::whereIn('slug', $slugs)->delete();
    }
};
