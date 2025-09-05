<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\Auth\UserAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserAuthController extends Controller
{
    protected $userAuthService;

    public function __construct(UserAuthService $userAuthService)
    {
        $this->userAuthService = $userAuthService;
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $result = $this->userAuthService->registerUser($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المستخدم بنجاح!',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $result = $this->userAuthService->loginUser($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $result['token'],
                'posts'=>$result['posts'],
                'user' => $result['user']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    public function logout(Request $request)
    {
        $this->userAuthService->logoutUser($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function refresh(Request $request)
    {
        $newToken = $this->userAuthService->refreshUserToken($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تجديد التوكن بنجاح',
            'token' => $newToken
        ]);
    }

    public function user(Request $request)
    {
        $id = $request->user()->id;

        $user = Cache::remember("user_profile_{$id}", now()->addMinutes(15), function () use ($request) {
            return $this->userAuthService->getAuthenticatedUser($request->user());
        });
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }
}