<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserAuthService
{
    public function registerUser(array $data): array
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'age' => $data['age'],
                'password' => Hash::make($data['password']),
            ]);

            // تعيين الدور
            // Assign role
            $role = Cache::rememberForever('user-role', function () {
                return Role::firstOrCreate([
                    'name' => 'user',
                    'guard_name' => 'api',
                ]);
            });

            $user->assignRole($role);

            // إنشاء التوكن باستخدام JWT
            $token = auth('api')->login($user);
            // ✅ Cache the token for reuse (e.g., 60 minutes)
            Cache::put("user:{$user->id}:token", $token, now()->addMinutes(60));

            return [
                'user' => $user->load('roles'),
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
            throw new \Exception('فشل عملية التسجيل', 500);
        }
    }

    public function loginUser(array $credentials): array
    {
        try {
            if (!$token = auth('api')->attempt($credentials)) {
                throw new \Exception('بيانات الاعتماد غير صحيحة', 401);
            }
            $user = Auth::guard('api')->user();

            // ✅ Cache token for reuse
            Cache::put("user:{$user->id}:token", $token, now()->addMinutes(60));
            return [
                'user' =>  $user->load('roles'),
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function logoutUser(): void
    {
        try {
            auth('api')->logout();
        } catch (\Exception $e) {
            Log::error('Logout failed');
            throw new \Exception('فشل في تسجيل الخروج', 500);
        }
    }

    public function refreshUserToken(): string
    {
        try {
            return auth('api')->refresh();
        } catch (\Exception $e) {
            Log::error('Token refresh failed');
            throw new \Exception('فشل في تجديد التوكن', 500);
        }
    }
    public function getAuthenticatedUser($user)
{
    return $user->only([
        'id',
        'name',
        'email',
        'created_at',
        'updated_at',
    ]);
}

}
