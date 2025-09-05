<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{

 protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('auth:api');
        $this->userService = $userService;
    }
    public function search(Request $request)
    {
        $query = User::query();
    
        // فلترة بالاسم إذا موجود
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }
    
        // فلترة بالإيميل إذا موجود
        if ($request->filled('email')) {
            $query->where('email', 'LIKE', '%' . $request->email . '%');
        }
    
        // لاحقاً فيك تضيف فلترة حسب العمر أو غيره بنفس الطريقة
    
        $users = $query->get();
    
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
    public function allUsers()
{
    $user = auth()->user();

    $allUsers = User::where('id', '!=', $user->id)->with('profileImage')->get();

    $friends = $user->friends()->with('profileImage')->get();

    $receivedRequests = Friendship::where('friend_id', $user->id)
        ->where('status', 'pending')
        ->with('user:id,name,email') // المرسل
        ->get()
        ->map(function ($req) {
            return [
                'id' => $req->user->id,
                'name' => $req->user->name,
                'email' => $req->user->email,
            ];
        });

    $sentRequests = Friendship::where('user_id', $user->id)
        ->where('status', 'pending')
        ->with('friend:id,name,email') // المستلم
        ->get()
        ->map(function ($req) {
            return [
                'id' => $req->friend->id,
                'name' => $req->friend->name,
                'email' => $req->friend->email,
            ];
        });

    return response()->json([
        'all_users' => $allUsers,
        'friends' => $friends,
        'received_requests' => $receivedRequests,
        'sent_requests' => $sentRequests,
    ]);
}
public function profile($id = null)
{
    $user = $this->userService->getFullProfile($id);

    return response()->json([
        'success' => true,
        'data' => $user
    ]);
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
        
        $user = Cache::remember("user_profile_{$id}", now()->addMinutes(15), function () use ($id) {
            return User::findOrFail($id);
        });
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        Cache::forget("user_profile_{$id}");

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