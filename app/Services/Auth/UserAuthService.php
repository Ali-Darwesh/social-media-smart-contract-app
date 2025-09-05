<?php

namespace App\Services\Auth;

use App\Models\Post;
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
                'gender'=>$data['gender'],
                'password' => Hash::make($data['password']),
            ]);
    
            // إذا فيه صورة مرفوعة نخزنها
            if (isset($data['profile_image'])) {
                $path = $data['profile_image']->store('profile_images', 'public');
                $user->profileImage()->create([
                    'url' => $path
                ]);
            } else {
                // صورة افتراضية
                $user->profileImage()->create([
                    'url' => 'public/default/n.png' // احفظ صورة default في public/storage/default
                ]);
            }
    
            // تعيين الدور
            $role = Role::firstOrCreate([
                'name' => 'user',
                'guard_name' => 'api'
            ]);
    
            $user->assignRole($role);
    
            // إنشاء التوكن
            $token = auth('api')->login($user);
    
            return [
                'user' => $user->load('roles', 'profileImage'),
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
    
            $user = auth('api')->user()->load('roles', 'profileImage');
    
            // جلب البوستات مع بيانات صاحب البوست وصورته
            $posts = Post::with([
                    'author.profileImage', // صورة صاحب البوست
                    'images',
                    'videos',
                    'comments'
                ])
                ->withCount([
                    'likes as likes_count',
                    'dislikes as dislikes_count',
                ])
                ->with(['reactions' => function ($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->latest()
                ->take(10)
                ->get();
    
            return [
                'user'  => $user,
                'token' => $token,
                'posts' => $posts
            ];
        } catch (\Exception $e) {
            \Log::error('Login failed: ' . $e->getMessage());
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
