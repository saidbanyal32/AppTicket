<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_modules') || ! Schema::hasTable('sys_permissions')) {
            return;
        }

        $ticketModuleId = DB::table('sys_modules')->where('slug', 'ticket')->value('id') ?: (string) Str::uuid();

        DB::table('sys_modules')->updateOrInsert(
            ['slug' => 'ticket'],
            ['id' => $ticketModuleId, 'name' => 'Ticket', 'icon' => 'bi-ticket-detailed', 'is_active' => true, 'sort_no' => 50, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('sys_permissions')
            ->where(function ($query) {
                $query->where('code', 'like', 'tickets.%')
                    ->orWhere('name', 'like', 'tickets.%')
                    ->orWhere('permission_slug', 'like', 'tickets.%');
            })
            ->update([
                'module_id' => $ticketModuleId,
                'module' => 'ticket',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('sys_permissions')
            ->where(function ($query) {
                $query->where('code', 'like', 'tickets.%')
                    ->orWhere('name', 'like', 'tickets.%')
                    ->orWhere('permission_slug', 'like', 'tickets.%');
            })
            ->update([
                'module_id' => null,
                'module' => 'tickets',
                'updated_at' => now(),
            ]);
    }
};
