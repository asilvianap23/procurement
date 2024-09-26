<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{
	public function run()
	{
		$permissions = [
			'users' => [
				'manage users',
				'view users',
				'create users',
				'edit users',
				'delete users',
			],
			'roles' => [
				'manage roles',
				'view roles',
				'create roles',
				'edit roles',
				'delete roles',
			],
			'permissions' => [
				'manage permissions',
				'view permissions',
				'create permissions',
				'edit permissions',
				'delete permissions',
			],
			'pengajuan' => [
				'manage pengajuan',
				'view pengajuan',
				'create pengajuan',
				'edit pengajuan',
				'delete pengajuan',
				'approve pengajuan',
				'export pengajuan',
				'import pengajuan',
			],
			'rekap pengajuan' => [
				'manage rekap pengajuan',
				'view rekap pengajuan',
				'export rekap pengajuan',
			],
		];
		
		$adminRole = Role::firstOrCreate(['name' => 'admin']);
		
		foreach ($permissions as $module => $modulePermissions) {
			foreach ($modulePermissions as $permission) {
				$perm = Permission::updateOrCreate(
					['name' => $permission, 'module' => $module],
					['name' => $permission, 'module' => $module]
				);
				$adminRole->givePermissionTo($perm);
			}
		}
	}
}