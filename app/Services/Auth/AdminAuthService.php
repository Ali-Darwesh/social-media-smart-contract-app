<?php

namespace App\Services\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminAuthService
{
    public function registerAdmin(array $data): array
    {
        try {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $role = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'admin-api'
            ]);

            $admin->assignRole($role);

            $token = auth('admin-api')->login($admin);

            return [
                'admin' => $admin->load('roles'),
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('Admin registration failed: ' . $e->getMessage());
            throw new \Exception('فشل في تسجيل المسؤول', 500);
        }
    }

    public function loginAdmin(array $credentials): array
    {
        if (!$token = auth('admin-api')->attempt($credentials)) {
            throw new \Exception('بيانات الاعتماد غير صحيحة', 401);
        }

        return [
            'admin' => auth('admin-api')->user()->load('roles'),
            'token' => $token
        ];
    }

    public function logoutAdmin(): void
    {
        auth('admin-api')->logout();
    }

    public function refreshToken(): string
    {
        return auth('admin-api')->refresh();
    }
}
