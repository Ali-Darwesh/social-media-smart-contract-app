<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin-api');
    }

    // Get all admins
    public function index()
    {
        $admins = Admin::with('roles')->get();
        
        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    // Get single admin
    public function show($id)
    {
        $admin = Admin::with('roles')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    // Create new admin
    public function store(Request $request)
    {
       //
    }

    // Update admin
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        if ($admin->id !== auth()->id()) {
            return response()->json([
            'success' => 'false',
            'message' => 'You cannot update others account'
        ]);}
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:admins,email,'.$id,
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $admin->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'data' => $admin
        ]);
    }

    // Delete admin
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent self-deletion
        if ($admin->id !== auth()->id()) {
            return response()->json([
            'success' => 'false',
            'message' => 'You cannot delete others account'
        ]);
            
        }
        
        $admin->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }

    // Get all supervisors
    public function allSupportTeamMember()
    {
        $supervisors = Supervisor::with('roles')->get();
        
        return response()->json([
            'success' => true,
            'data' => $supervisors
        ]);
    }

    // Delete supervisor
    public function deleteSupportMember($id)
    {
        $supervisor = Supervisor::findOrFail($id);
        $supervisor->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Support member deleted successfully'
        ]);
    }
}