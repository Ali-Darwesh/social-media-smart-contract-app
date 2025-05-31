<?php

namespace App\Http\Controllers;

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;

class SupervisorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:supervisor-api');
    }

    /**
     * Get all supervisors (requires manage_support permission)
     */
    public function index()
    {
        //$this->authorize('manage_support');

        $supervisors = Supervisor::with('roles')->get();

        return response()->json([
            'success' => true,
            'data' => $supervisors
        ]);
    }

    /**
     * Get single supervisor (requires manage_support permission)
     */
    public function show($id)
    {


        $supervisor = Supervisor::with('roles')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $supervisor
        ]);
    }

    /**
     * Update supervisor profile (can only update own profile)
     */
    public function update(Request $request, $id)
    {
        $supervisor = Supervisor::findOrFail($id);

        if (Auth::id() != $id) {
            throw new AuthorizationException('You can only update your own profile');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:supervisors,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $supervisor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supervisor updated successfully',
            'data' => $supervisor
        ]);
    }

    /**
     * Ban a user (requires manage_users permission)
     */
    // public function banUser($userId)
    // {

    //     $user = User::findOrFail($userId);
    //     $user->update(['is_banned' => true]);

    //     // Log the ban action
    //     activity()
    //         ->causedBy(Auth::user())
    //         ->performedOn($user)
    //         ->log('banned user');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User banned successfully'
    //     ]);
    // }

    /**
     * Update user information (requires manage_users permission)
     */
    public function updateUser(Request $request, $userId)
    {
        $this->authorize('manage_users', User::class);

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }
}
