<?php

namespace Database\Seeders;

use App\Models\Master\RefCostCode;
use App\Models\Master\RefItemCategory;
use App\Models\Master\RefItemUnit;
use App\Models\Master\RefJabatan;
use App\Models\Master\RefTicketCategory;
use App\Models\Master\RefTicketSla;
use App\Models\Master\RefUnit;
use App\Models\Master\RefVendorType;
use App\Models\Master\SysPermission;
use App\Models\Master\SysRole;
use App\Models\Master\SysUser;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $restoreOrCreate = function (string $model, array $where, array $values = []) {
            $record = $model::withTrashed()->where($where)->first();

            if ($record) {
                $record->restore();
                $record->fill($values)->save();

                return $record;
            }

            return $model::create($where + $values);
        };

        $unit = $restoreOrCreate(RefUnit::class, ['code' => 'HO'], ['name' => 'Head Office', 'is_active' => true]);
        $projectUnit = $restoreOrCreate(RefUnit::class, ['code' => 'PRJ'], ['name' => 'Project Operation', 'parent_id' => $unit->id, 'is_active' => true]);

        $jabatan = $restoreOrCreate(RefJabatan::class, ['code' => 'ADM'], ['name' => 'Administrator', 'level' => 1, 'is_active' => true]);
        $restoreOrCreate(RefJabatan::class, ['code' => 'PM'], ['name' => 'Project Manager', 'level' => 2, 'is_active' => true]);

        $restoreOrCreate(SysUser::class,
            ['username' => 'admin'],
            [
                'unit_id' => $unit->id,
                'jabatan_id' => $jabatan->id,
                'employee_no' => 'EMP-0001',
                'name' => 'Administrator',
                'email' => 'admin@zainerp.local',
                'password' => 'password',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'administrator@example.test'],
            ['name' => 'Administrator', 'password' => 'password']
        );

        $role = $restoreOrCreate(SysRole::class, ['code' => 'SUPERADMIN'], ['name' => 'Super Administrator', 'is_active' => true]);

        foreach (config('master-data') as $key => $config) {
            $restoreOrCreate(SysPermission::class,
                ['code' => $key.'.view'],
                ['module' => $config['group'], 'name' => 'View '.$config['title']]
            );
            $restoreOrCreate(SysPermission::class,
                ['code' => $key.'.manage'],
                ['module' => $config['group'], 'name' => 'Manage '.$config['title']]
            );
        }

        $role->permissions()->syncWithoutDetaching(SysPermission::pluck('id')->all());

        $restoreOrCreate(RefItemCategory::class, ['code' => 'MAT'], ['name' => 'Material', 'is_active' => true]);
        $restoreOrCreate(RefItemUnit::class, ['code' => 'PCS'], ['name' => 'Pieces']);
        $restoreOrCreate(RefItemUnit::class, ['code' => 'M3'], ['name' => 'Cubic Meter']);

        $restoreOrCreate(RefCostCode::class, ['code' => '1000', 'project_id' => null], ['name' => 'Direct Cost', 'level' => 1, 'type' => 'material', 'is_active' => true]);
        $restoreOrCreate(RefVendorType::class, ['code' => 'SUP'], ['name' => 'Supplier', 'is_active' => true]);
        $restoreOrCreate(RefVendorType::class, ['code' => 'SUB'], ['name' => 'Subcontractor', 'is_active' => true]);

        foreach (['low' => [120, 2880], 'medium' => [60, 1440], 'high' => [30, 480], 'critical' => [15, 240]] as $priority => [$response, $resolve]) {
            $restoreOrCreate(RefTicketSla::class,
                ['name' => ucfirst($priority).' SLA'],
                ['priority' => $priority, 'response_minutes' => $response, 'resolve_minutes' => $resolve]
            );
        }

        $restoreOrCreate(RefTicketCategory::class,
            ['code' => 'GENERAL'],
            ['name' => 'General Support', 'sla_id' => RefTicketSla::where('priority', 'medium')->value('id'), 'is_active' => true, 'sort_no' => 1]
        );

        foreach ([
            'app_name' => ['SupportDesk Pro', 'string', 'Application display name'],
            'company_name' => ['Zain ERP', 'string', 'Company name'],
            'default_ticket_sla' => ['medium', 'string', 'Default ticket SLA priority'],
            'allow_attachment' => ['1', 'boolean', 'Enable ticket attachment upload'],
            'max_upload_size' => ['10240', 'integer', 'Maximum upload size in kilobytes'],
        ] as $key => [$value, $type, $description]) {
            Setting::firstOrCreate(['key' => $key], compact('value', 'type', 'description'));
        }
    }
}
