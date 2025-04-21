<?php

namespace App\Services\Auth;

use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SupervisorAuthService
{
    public function registerSupervisor(array $data): array
    {
        try {
            $supervisor = Supervisor::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $role = Role::firstOrCreate([
                'name' => 'supervisor',
                'guard_name' => 'supervisor-api'
            ]);

            $supervisor->assignRole($role);

            $token = auth('supervisor-api')->login($supervisor);

            return [
                'supervisor' => $supervisor->load('roles'),
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('Supervisor registration failed: ' . $e->getMessage());
            throw new \Exception('فشل في تسجيل المشرف', 500);
        }
    }

    public function loginSupervisor(array $credentials): array
    {
        if (!$token = auth('supervisor-api')->attempt($credentials)) {
            throw new \Exception('بيانات الاعتماد غير صحيحة', 401);
        }

        return [
            'supervisor' => auth('supervisor-api')->user()->load('roles'),
            'token' => $token
        ];
    }

    public function logoutSupervisor(): void
    {
        auth('supervisor-api')->logout();
    }

    public function refreshToken(): string
    {
        return auth('supervisor-api')->refresh();
    }
}
