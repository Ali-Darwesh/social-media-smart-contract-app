<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginSupervisorRequest;
use App\Http\Requests\Auth\RegisterSupervisorRequest;
use App\Services\Auth\SupervisorAuthService;
use Illuminate\Http\Request;

class SupervisorAuthController extends Controller
{
    protected $supervisorAuthService;

    public function __construct(SupervisorAuthService $supervisorAuthService)
    {
        $this->supervisorAuthService = $supervisorAuthService;
        $this->middleware('auth:admin-api')->only('register');
    }

    public function register(RegisterSupervisorRequest $request)
    {
        $this->authorize('create supervisor account');
        try {
            $result = $this->supervisorAuthService->registerSupervisor($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المشرف بنجاح!',
                'supervisor' => $result['supervisor'],
                'token' => $result['token'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(LoginSupervisorRequest $request)
    {
        try {
            $result = $this->supervisorAuthService->loginSupervisor($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $result['token'],
                'supervisor' => $result['supervisor']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ],  401);
        }
    }

    public function logout(Request $request)
    {
        $this->supervisorAuthService->logoutSupervisor($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function refresh(Request $request)
    {
        $newToken = $this->supervisorAuthService->refreshSupervisorToken($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'تم تجديد التوكن بنجاح',
            'token' => $newToken
        ]);
    }

    public function supervisor(Request $request)
    {
        $supervisor = $this->supervisorAuthService->getAuthenticatedSupervisor($request->user());
        
        return response()->json([
            'success' => true,
            'supervisor' => $supervisor
        ]);
    }
}