<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::user() instanceof User && Auth::id() != $id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own profile'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::user() instanceof User && Auth::id() != $id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own account'
            ], 403);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}