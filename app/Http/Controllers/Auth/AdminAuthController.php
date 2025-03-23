<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('authToken')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'admin registered successfully!',
            'admin' => $admin,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $admin = Admin::where('email', $request->email)->first();
    
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
    
        $token = $admin->createToken('authToken')->accessToken;
    
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'admin' => $admin
        ]);
    }

    
    public function logout(Request $request)
    {
        $request->admin()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh(Request $request)
    {
        $admin = $request->admin();
        
        
        $request->admin()->token()->revoke();
        
        
        $newToken = $admin->createToken('authToken')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'token' => $newToken
        ]);
    }


    public function admin(Request $request)
    {
        return response()->json([
            'success' => true,
            'admin' => $request->admin()
        ]);
    }
}
