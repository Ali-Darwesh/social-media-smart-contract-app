<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\SupervisorAuthController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SupervisorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Contract\ClauseController;
use App\Http\Controllers\Contract\ContractController;
use App\Http\Controllers\Contract\ContractTransactionsController;
use App\Http\Controllers\FriendshipController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

// Broadcast::routes(['middleware' => ['auth:api']]);

Route::middleware('auth:api')->group(function () {
    Route::get('/contracts', [ContractController::class, 'index']);

    // إنشاء عقد جديد
    Route::post('/contracts', [ContractController::class, 'store']);

    // عرض عقد معين
    Route::get('/contracts/{id}', [ContractController::class, 'show']);

    // توقيع عقد
    Route::post('/contracts/{id}/sign', [ContractController::class, 'sign']);

    // تغيير حالة العقد (مثلاً rejected / canceled / active)
    Route::patch('/contracts/{id}/status', [ContractController::class, 'updateStatus']);

    // حذف عقد
    Route::delete('/contracts/{id}', [ContractController::class, 'destroy']);


    Route::post('/friends/request/{id}', [FriendshipController::class, 'sendRequest']);
    Route::post('/friends/accept/{id}', [FriendshipController::class, 'acceptRequest']);
    Route::delete('/friends/decline/{id}', [FriendshipController::class, 'declineRequest']);
    Route::delete('/friends/cancel/{id}', [FriendshipController::class, 'cancelRequest']);
    Route::delete('/friends/remove/{id}', [FriendshipController::class, 'unfriend']);

    Route::get('/friends', [FriendshipController::class, 'friends']);
    Route::get('/friends/requests/incoming', [FriendshipController::class, 'incomingRequests']);
    Route::get('/friends/requests/sent', [FriendshipController::class, 'sentRequests']);
});
// Public routes
Route::prefix('auth')->group(function () {
    // User authentication routes
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);

    // Admin authentication routes
    Route::prefix('admin')->group(function () {
        Route::post('/register', [AdminAuthController::class, 'register']);
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    // Supervisor authentication routes

    Route::prefix('supervisor')->group(function () {
        Route::middleware(['auth:admin-api', 'can:create supervisor account'])->group(function () {
            Route::post('/register', [SupervisorAuthController::class, 'register']);
        });
        Route::post('/login', [SupervisorAuthController::class, 'login']);
    });
});
Route::get('posts', [PostController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    // المحادثات والرسائل
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::get('/messages/{chat_id}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // المنشورات
    Route::prefix('posts')->group(function () {
        Route::get('/{id}', [PostController::class, 'show']);
        Route::post('/store', [PostController::class, 'store'])->middleware('can:create post');
        Route::put('/update/{post}', [PostController::class, 'update'])->middleware('can:update own post');
        Route::delete('/destroy/{post}', [PostController::class, 'destroy'])->middleware('can:delete own post');
    });

    Route::post('/posts/{post}/like', [PostController::class, 'addLike']);
    Route::post('/posts/{post}/dislike', [PostController::class, 'addDislike']);
    Route::delete('/posts/{post}/reaction', [PostController::class, 'removeReaction']);


    // التعليقات
    Route::prefix('comments')->group(function () {
        Route::post('/store', [CommentController::class, 'store'])->middleware('can:create comment');
        Route::put('/update/{comment}', [CommentController::class, 'update']);
        Route::delete('/destroy/{comment}', [CommentController::class, 'destroy'])->middleware('can:delete own comment');
    });
});
Route::prefix('supervisor')->middleware(['auth:supervisor-api'])->group(function () {
    Route::get('/profile', [SupervisorAuthController::class, 'supervisor']);

    Route::prefix('supervisors')->group(function () {
        Route::put('/update/{id}', [SupervisorController::class, 'update']);
        Route::get('/', [SupervisorController::class, 'index']);
        Route::get('/show/{id}', [SupervisorController::class, 'show']);
    });

    Route::prefix('users')->group(function () {
        Route::put('/{userId}', [SupervisorController::class, 'updateUser']);
        Route::post('/{userId}/ban', [SupervisorController::class, 'banUser'])->middleware('can:ban user');
    });

    Route::prefix('comments')->group(function () {
        Route::delete('/destroy/{comment}', [CommentController::class, 'destroy'])->middleware('can:delete any comment');
    });

    Route::prefix('posts')->group(function () {
        Route::delete('/destroy/{post}', [PostController::class, 'destroy'])->middleware('can:delete any post');
    });
});

Route::prefix('admin')->middleware(['auth:admin-api'])->group(function () {
    Route::get('/profile', [AdminAuthController::class, 'admin']);

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
    });

    Route::prefix('supervisor')->group(function () {
        Route::post('/register', [SupervisorAuthController::class, 'register'])->middleware('can:create supervisor account');
    });

    Route::get('/admins', [AdminController::class, 'index']);
    Route::get('/admin/show/{id}', [AdminController::class, 'show']);
    Route::put('/admin/update/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/destroy/{id}', [AdminController::class, 'destroy'])->middleware('can:delete supervisor account');
});

Route::get('/noti',   function () {
    // يمكن للجميع رؤية المنشورات
    $not = DB::table('notifications')->get();


    return response()->json([
        'success' => true,
        'data' => $not
    ]);
});


/////////////////////////////////////

////    S M A R T _ C O N T R A C T S

/////////////////////////////////////
Route::post('/approve_clause', [ContractTransactionsController::class, 'approveClause']);
Route::post('/execute_clause', [ContractTransactionsController::class, 'executeClause']);
Route::post('/reject_contract', [ContractTransactionsController::class, 'rejectContract']);
Route::get('/get_clauses_count', [ContractTransactionsController::class, 'getClausesCount']);
Route::get('/get_clauses', [ContractTransactionsController::class, 'getClause']);
Route::get('/get_status', [ContractTransactionsController::class, 'getStatus']);


//////

Route::post('/{id}/deploy', [ContractTransactionsController::class, 'deploy']);
Route::post('/add_clause/{id}', [ClauseController::class, 'create']);
Route::get('/my_contracts', [ContractController::class, 'myContracts']);
Route::post('/create_contract', [ContractController::class, 'store']);
Route::post('/{contractId}/send_invite', [ContractController::class, 'sendInvite']);
Route::get('/get_invites', [ContractController::class, 'getInvites']);
Route::post('/{contractId}/respond_invite', [ContractController::class, 'respondInvite']);
Route::get('/{id}/get_clauses_DB', [ClauseController::class, 'getClauses']);
Route::get('/{id}/get_approved_clauses', [ClauseController::class, 'getApprovedClauses']);
Route::post('/update_clause/{clause}', [ClauseController::class, 'update']);
Route::post('/accepte_clause/{id}', [ClauseController::class, 'accepteClause']);
Route::post('/delete/{id}', [ClauseController::class, 'destroy']);




/*
Route::middleware('auth:api')->group(function () {
    
    // كل المحادثات (الشاتات) الخاصة بالمستخدم
    Route::get('/chats', [ChatController::class, 'index']);

    // عرض محادثة محددة (تشمل الرسائل)
    Route::get('/chats/{id}', [ChatController::class, 'show']);

    // كل الرسائل ضمن شات محدد
    Route::get('/messages/{chat_id}', [MessageController::class, 'index']);

    // إرسال رسالة (إنشاء أو استخدام شات بين الطرفين)
    Route::post('/messages', [MessageController::class, 'store']);
});

// Public routes
Route::prefix('auth')->group(function () {
    // User authentication routes
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    
    // Admin authentication routes
 Route::prefix('admin')->group(function () {
        Route::post('/register', [AdminAuthController::class, 'register']);
        Route::post('/login', [AdminAuthController::class, 'login']);
    });
    
    // Supervisor authentication routes
    
Route::prefix('supervisor')->group(function () {
        Route::middleware(['auth:admin-api', 'can:create supervisor account'])->group(function () {
        Route::post('/register', [SupervisorAuthController::class, 'register']);
        });
        Route::post('/login', [SupervisorAuthController::class, 'login']);
    });
});

// Authenticated routes (for all user types)
Route::middleware('auth:api,admin-api,supervisor-api')->group(function () {
    // User routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [UserAuthController::class, 'logout']);
        Route::post('/refresh', [UserAuthController::class, 'refresh']);
        Route::get('/user', [UserAuthController::class, 'user']);
    });
 // Admin routes
 Route::prefix('auth/admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::post('/refresh', [AdminAuthController::class, 'refresh']);
    Route::get('/profile', [AdminAuthController::class, 'admin']);
});
// Supervisor routes
Route::prefix('auth/supervisor')->group(function () {
    Route::post('/logout', [SupervisorAuthController::class, 'logout']);
    Route::post('/refresh', [SupervisorAuthController::class, 'refresh']);
    Route::get('/profile', [SupervisorAuthController::class, 'supervisor']);
});
     // Post routes (accessible by all authenticated users)
     Route::prefix('posts')->group(function () {
        Route::get('/',[PostController::class,'index']);
        Route::get('/{id}',[PostController::class,'show']);
        Route::post('/store', [PostController::class, 'store']);
        Route::put('/update/{post}', [PostController::class, 'update']);
        Route::delete('/destroy/{post}', [PostController::class, 'destroy']);
    });
    // Admins CRUD
    Route::get('/admins', [AdminController::class, 'index']); // Get all admins
   // Route::post('/admin/store', [AdminController::class, 'store']); // Create new admin
    Route::get('/admin/show/{id}', [AdminController::class, 'show']); // Get single admin
    Route::put('/admin/update/{id}', [AdminController::class, 'update']); // Update admin
    Route::delete('/admin/destroy/{id}', [AdminController::class, 'destroy']); // Delete admin

    Route::prefix('supervisors')->group(function () {
        // Supervisor profile
        Route::put('/update/{id}', [SupervisorController::class, 'update']);
        Route::get('/', [SupervisorController::class, 'index']);
        Route::get('/show/{id}', [SupervisorController::class, 'show']);
    });
      // Requires manage_users permission
      Route::prefix('users')->group(function () {
        Route::put('/{userId}', [SupervisorController::class, 'updateUser']);
        Route::post('/{userId}/ban', [SupervisorController::class, 'banUser']);
    });


      Route::prefix('comments')->group(function () {
         Route::post('/store', [CommentController::class, 'store']);
         Route::put('/update/{comment}', [CommentController::class, 'update']);
         Route::delete('/destroy/{comment}', [CommentController::class, 'destroy']);
    });

// Public comment routes
Route::prefix('comments')->group(function () {
Route::get('/', [CommentController::class, 'index']);
Route::get('/show/{comment}', [CommentController::class, 'show']);
});
});


*/


/*



Route::middleware('auth:admin-api')->group(function () {
    // Admin routes
    Route::prefix('auth/admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
        Route::get('/profile', [AdminAuthController::class, 'admin']);
    });
});

Route::middleware('auth:supervisor-api')->group(function () {
    // Supervisor routes
    Route::prefix('auth/supervisor')->group(function () {
        Route::post('/logout', [SupervisorAuthController::class, 'logout']);
        Route::post('/refresh', [SupervisorAuthController::class, 'refresh']);
        Route::get('/profile', [SupervisorAuthController::class, 'supervisor']);
    });
});
    
    // Post routes (accessible by all authenticated users)
    Route::prefix('posts')->group(function () {
        Route::get('/',[PostController::class,'index']);
        Route::get('/{id}',[PostController::class,'show']);
        Route::post('/store', [PostController::class, 'store']);
        Route::put('/update/{post}', [PostController::class, 'update']);
        Route::delete('/destroy/{post}', [PostController::class, 'destroy']);
    });


// Admin-only routes
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'adminIndex']);
    });
    
    // Additional admin-only routes can be added here
});

// Supervisor-only routes
Route::middleware(['auth:api', 'role:supervisor'])->prefix('supervisor')->group(function () {
    // Add supervisor-specific routes here
    // Example:
    // Route::get('/dashboard', [SupervisorDashboardController::class, 'index']);
});

// Admin Routes Group
Route::middleware('auth:admin-api')->group(function () {
    // Admins CRUD
    Route::get('/admins', [AdminController::class, 'index']); // Get all admins
   // Route::post('/admin/store', [AdminController::class, 'store']); // Create new admin
    Route::get('/admin/show/{id}', [AdminController::class, 'show']); // Get single admin
    Route::put('/admin/update/{id}', [AdminController::class, 'update']); // Update admin
    Route::delete('/admin/destroy/{id}', [AdminController::class, 'destroy']); // Delete admin
    // Support Team Management
    Route::get('/admin/allSupportTeamMember', [AdminController::class, 'allSupportTeamMember']); // Get all supervisors
    Route::delete('/admin/deleteSupportMember/{id}', [AdminController::class, 'deleteSupportMember']); // Delete supervisor
  
});
Route::prefix('supervisors')->middleware('auth:supervisor-api')->group(function () {
    // Supervisor profile
    Route::put('/update/{id}', [SupervisorController::class, 'update']);
    
  
        Route::get('/', [SupervisorController::class, 'index']);
        Route::get('/show/{id}', [SupervisorController::class, 'show']);
   
    
    // Requires manage_users permission
    Route::prefix('users')->middleware('can:manage_users')->group(function () {
        Route::put('/{userId}', [SupervisorController::class, 'updateUser']);
        Route::post('/{userId}/ban', [SupervisorController::class, 'banUser']);
    });
});
Route::prefix('comments')->group(function () {
    Route::post('/store', [CommentController::class, 'store']);
    Route::put('/update/{comment}', [CommentController::class, 'update']);
    Route::delete('/destroy/{comment}', [CommentController::class, 'destroy']);
});


// Public comment routes
Route::prefix('comments')->group(function () {
Route::get('/', [CommentController::class, 'index']);
Route::get('/show/{comment}', [CommentController::class, 'show']);
});*/