<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    // عرض كل العقود المرتبطة بالمستخدم الحالي
    public function index()
    {
        $contracts = Auth::user()->contracts()->with('users')->get();
        return response()->json($contracts);
    }

    // إنشاء عقد جديد
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id'
        ]);

        $contract = Contract::create([
            'title' => $request->title,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        // ربط المستخدم الحالي + المشاركين
        $contract->users()->attach(array_merge(
            [$request->user()->id],
            $request->participants
        ));

        return response()->json([
            'message' => 'تم إنشاء العقد بنجاح',
            'contract' => $contract
        ], 201);
    }

    // عرض عقد واحد
    public function show($id)
    {
        $contract = Contract::with('users')->findOrFail($id);

        if (! $contract->users->contains(Auth::id())) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        return response()->json($contract);
    }

    // توقيع العقد من قبل مستخدم
    public function sign($id)
    {
        $contract = Contract::findOrFail($id);

        if (! $contract->users->contains(Auth::id())) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        // مثال بسيط، نغير الحالة إذا وقع الجميع (بسيطة الآن بدون تتبع توقيعات فردية)
        $signedUsers = $contract->signed_users ?? [];
        if (! in_array(Auth::id(), $signedUsers)) {
            $signedUsers[] = Auth::id();
            $contract->signed_users = $signedUsers;

            if (count($signedUsers) === $contract->users()->count()) {
                $contract->status = 'signed';
            }

            $contract->save();
        }

        return response()->json(['message' => 'تم التوقيع']);
    }

    // تغيير حالة العقد (مثلاً إلغاء أو قبول...)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,signed,rejected,canceled,active,expired'
        ]);

        $contract = Contract::findOrFail($id);

        if (! $contract->users->contains(Auth::id())) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $contract->status = $request->status;
        $contract->save();

        return response()->json(['message' => 'تم تحديث الحالة']);
    }

    // حذف عقد (اختياري)
    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status === 'signed') {
            return response()->json(['error' => 'لا يمكن حذف عقد موقع'], 403);
        }

        if (! $contract->users->contains(Auth::id())) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $contract->delete();

        return response()->json(['message' => 'تم الحذف']);
    }
}
