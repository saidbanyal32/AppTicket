<?php

namespace Database\Seeders;

use App\Models\Master\RefCostCode;
use App\Models\Master\RefItemCategory;
use App\Models\Master\RefItemUnit;
use App\Models\Master\RefJabatan;
use App\Models\Master\RefUnit;
use App\Models\Master\RefVendorType;
use App\Models\Master\SysPermission;
use App\Models\Master\SysRole;
use App\Models\Master\SysUser;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $unit = RefUnit::firstOrCreate(['code' => 'HO'], ['name' => 'Head Office', 'is_active' => true]);
        $projectUnit = RefUnit::firstOrCreate(['code' => 'PRJ'], ['name' => 'Project Operation', 'parent_id' => $unit->id, 'is_active' => true]);

        $jabatan = RefJabatan::firstOrCreate(['code' => 'ADM'], ['name' => 'Administrator', 'level' => 1, 'is_active' => true]);
        RefJabatan::firstOrCreate(['code' => 'PM'], ['name' => 'Project Manager', 'level' => 2, 'is_active' => true]);

        SysUser::firstOrCreate(
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

        $role = SysRole::firstOrCreate(['code' => 'SUPERADMIN'], ['name' => 'Super Administrator', 'is_active' => true]);

        foreach (config('master-data') as $key => $config) {
            SysPermission::firstOrCreate(
                ['code' => $key.'.view'],
                ['module' => $config['group'], 'name' => 'View '.$config['title']]
            );
            SysPermission::firstOrCreate(
                ['code' => $key.'.manage'],
                ['module' => $config['group'], 'name' => 'Manage '.$config['title']]
            );
        }

        $role->permissions()->syncWithoutDetaching(SysPermission::pluck('id')->all());

        RefItemCategory::firstOrCreate(['code' => 'MAT'], ['name' => 'Material', 'is_active' => true]);
        RefItemUnit::firstOrCreate(['code' => 'PCS'], ['name' => 'Pieces']);
        RefItemUnit::firstOrCreate(['code' => 'M3'], ['name' => 'Cubic Meter']);

        RefCostCode::firstOrCreate(['code' => '1000', 'project_id' => null], ['name' => 'Direct Cost', 'level' => 1, 'type' => 'material', 'is_active' => true]);
        RefVendorType::firstOrCreate(['code' => 'SUP'], ['name' => 'Supplier', 'is_active' => true]);
        RefVendorType::firstOrCreate(['code' => 'SUB'], ['name' => 'Subcontractor', 'is_active' => true]);
    }
}
