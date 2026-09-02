<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = ['admin', 'company', 'worker'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = ['users.manage', 'companies.manage', 'workers.manage', 'jobs.manage', 'applications.manage', 'payments.manage', 'reports.manage', 'settings.manage'];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::findByName('admin', 'web')->syncPermissions($permissions);
    }
}
