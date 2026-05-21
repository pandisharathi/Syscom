<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move Reports and Settings to the end of the sidebar
        \DB::table('menus')
            ->where('name', 'Reports')
            ->update(['sort_order' => 950]);
        \DB::table('menus')
            ->where('name', 'Settings')
            ->update(['sort_order' => 960]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the sort order changes
        \DB::table('menus')
            ->where('name', 'Reports')
            ->update(['sort_order' => null]);
        \DB::table('menus')
            ->where('name', 'Settings')
            ->update(['sort_order' => null]);
    }
};
