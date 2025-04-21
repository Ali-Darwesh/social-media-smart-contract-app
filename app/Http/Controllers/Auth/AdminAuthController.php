<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAdminRequest;
use App\Http\Requests\Auth\RegisterAdminRequest;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    protected $adminAuthService;

    public function __construct(AdminAuthService $adminAuthService)
    {
        $this->adminAuthService = $adminAuthService;
    }

    public function register(RegisterAdminRequest $request)
    {
        try {
            $result = $this->adminAuthService->registerAdmin($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المسؤول بنجاح!',
                'admin' => $result['admin'],
                'token' => $result['token'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(LoginAdminRequest $request)
    {
        try {
            $result = $this->adminAuthService->loginAdmin($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $result['token'],
                'admin' => $result['admin']
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
        $this->adminAuthService->logoutAdmin($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function refresh(Request $request)
    {
        $newToken = $this->adminAuthService->refreshToken($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تجديد التوكن بنجاح',
            'token' => $newToken
        ]);
    }

    public function admin(Request $request)
    {
        return response()->json([
            'success' => true,
            'admin' => $request->user()
        ]);
    }
}