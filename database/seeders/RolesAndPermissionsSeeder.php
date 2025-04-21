<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // الصلاحيات المخصصة لكل نوع مستخدم
    $permissions = [
        'api' => [
            'create post',
            'view posts',
            'update own post',
            'delete own post',
            'create comment',
            'delete own comment'
        ],
        'supervisor-api' => [
            'delete any post',
            'delete any comment',
            'ban user'
        ],
        'admin-api' => [
            'create supervisor account',
            'delete supervisor account',
            'manage all'
        ]
    ];

    // إنشاء الصلاحيات لكل حارس
    foreach ($permissions as $guard => $guardPermissions) {
        foreach ($guardPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard
            ]);
        }
    }

    // إنشاء الأدوار وتعيين الصلاحيات
    Role::create(['name' => 'user', 'guard_name' => 'api'])
        ->givePermissionTo($permissions['api']);

    Role::create(['name' => 'supervisor', 'guard_name' => 'supervisor-api'])
        ->givePermissionTo($permissions['supervisor-api']);

    Role::create(['name' => 'admin', 'guard_name' => 'admin-api'])
        ->givePermissionTo($permissions['admin-api']);
} 
        
        
        
        
        
        
        
        
        
        /*
        // مسح البيانات السابقة
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. إنشاء جميع الصلاحيات لكل حارس
        $allPermissions = [
            'create account',
            'delete account',
            'update account',
            'create post',
            'view posts',
            'update own post',
            'delete own post',
            'delete any post',
            'create comment',
            'delete own comment',
            'delete any comment',
            'ban user',
            'create supervisor account',
            'delete supervisor account',
            'manage all'
        ];

        // إنشاء الصلاحيات لكل حارس
        $guards = ['api', 'supervisor-api', 'admin-api'];
        foreach ($guards as $guard) {
            foreach ($allPermissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }
        }

        // 2. إنشاء الأدوار وتعيين الصلاحيات المناسبة لكل حارس
        
        // دور المستخدم العادي (حارس api)
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'api'])
            ->givePermissionTo([
                
                'delete account',
                'update account',
                'create post',
                'view posts',
                'update own post',
                'delete own post',
                'create comment',
                'delete own comment'
            ]);

        // دور فريق الدعم (حارس supervisor-api)
        $supervisorRole = Role::create(['name' => 'supervisor', 'guard_name' => 'supervisor-api'])
            ->givePermissionTo([
                'delete account',
                'update account',
                'delete any post',
                'delete any comment',
                'ban user',
                
            ]);

        // دور المسؤول (حارس admin-api)
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'admin-api'])
            ->givePermissionTo(
                Permission::where('guard_name', 'admin-api')->get()
            );
    }*/
}