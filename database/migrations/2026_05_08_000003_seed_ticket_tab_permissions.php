<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $tabs = [
        'my_request' => 'My Request',
        'need_assignment' => 'Need Assignment',
        'assign_to_me' => 'Assign To Me',
        'overdue' => 'Overdue',
        'closed' => 'Closed',
        'all' => 'All Tickets',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sys_permissions')) {
            return;
        }

        $moduleId = $this->moduleId();
        $actionIds = $this->actionIds();
        $permissionIds = [];

        foreach ($this->tabs as $tab => $label) {
            $slug = 'ticket.tab.'.$tab;
            $id = DB::table('sys_permissions')->where('code', $slug)->orWhere('name', $slug)->value('id');
            $values = [
                'module_id' => $moduleId,
                'action_id' => $actionIds[$tab] ?? null,
                'module' => 'ticket',
                'name' => $slug,
                'permission_name' => 'Ticket Tab '.$label,
                'permission_slug' => $slug,
                'guard_name' => 'web',
                'description' => 'Access ticket workflow tab: '.$label,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('sys_permissions', 'permission_type')) {
                $values['permission_type'] = 'workflow_tab';
            }

            if (! $id && $this->usesUuidPrimaryKey('sys_permissions')) {
                $values['id'] = (string) Str::uuid();
            }

            DB::table('sys_permissions')->updateOrInsert(
                ['code' => $slug],
                $values
            );

            $permissionIds[$tab] = DB::table('sys_permissions')->where('code', $slug)->value('id');
        }

        $this->grantToRoleCodes(['SUPERADMIN', 'ADMIN', 'ADMIN_TICKET'], array_values($permissionIds));
        $this->grantToRoleCodes(['USER'], [$permissionIds['my_request']]);
        $this->grantToRoleCodes(['PIC', 'PIC_TICKET', 'TICKET_PIC'], [
            $permissionIds['my_request'],
            $permissionIds['need_assignment'],
            $permissionIds['assign_to_me'],
            $permissionIds['overdue'],
            $permissionIds['closed'],
        ]);
        $this->grantToRoleCodes(['SUPERVISOR', 'MANAGER'], array_values($permissionIds));
    }

    public function down(): void
    {
        DB::table('sys_permissions')
            ->whereIn('code', collect(array_keys($this->tabs))->map(fn (string $tab) => 'ticket.tab.'.$tab)->all())
            ->delete();
    }

    private function moduleId(): ?string
    {
        if (! Schema::hasTable('sys_modules')) {
            return null;
        }

        $id = DB::table('sys_modules')->where('slug', 'ticket')->value('id') ?: (string) Str::uuid();
        DB::table('sys_modules')->updateOrInsert(
            ['slug' => 'ticket'],
            ['id' => $id, 'name' => 'Ticket', 'icon' => 'bi-ticket-detailed', 'is_active' => true, 'sort_no' => 50, 'created_at' => now(), 'updated_at' => now()]
        );

        return $id;
    }

    private function actionIds(): array
    {
        if (! Schema::hasTable('sys_actions')) {
            return [];
        }

        $ids = [];

        foreach (array_keys($this->tabs) as $index => $tab) {
            $id = DB::table('sys_actions')->where('slug', $tab)->value('id') ?: (string) Str::uuid();
            DB::table('sys_actions')->updateOrInsert(
                ['slug' => $tab],
                ['id' => $id, 'name' => Str::headline($tab), 'is_active' => true, 'sort_no' => 130 + ($index * 10), 'created_at' => now(), 'updated_at' => now()]
            );
            $ids[$tab] = $id;
        }

        return $ids;
    }

    private function grantToRoleCodes(array $codes, array $permissionIds): void
    {
        if (! Schema::hasTable('sys_roles') || ! Schema::hasTable('sys_role_permissions')) {
            return;
        }

        $roleIds = DB::table('sys_roles')->whereIn('code', $codes)->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $values = [];

                if (Schema::hasColumn('sys_role_permissions', 'id') && $this->usesUuidPrimaryKey('sys_role_permissions')) {
                    $values['id'] = (string) Str::uuid();
                }

                if (Schema::hasColumn('sys_role_permissions', 'created_at')) {
                    $values['created_at'] = now();
                    $values['updated_at'] = now();
                }

                DB::table('sys_role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    $values
                );
            }
        }
    }

    private function usesUuidPrimaryKey(string $table): bool
    {
        return DB::getSchemaBuilder()->getColumnType($table, 'id') === 'uuid';
    }
};
