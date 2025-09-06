<?php

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\InviteStatusUpdateRequest;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Models\Contract;
use App\Models\Friendship;
use App\Models\User;
use App\Services\Contract\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    protected $contractService;
    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;

        // سياسات المصادقة
        // $this->middleware('auth:api');

    }
    // عرض كل العقود المرتبطة بالمستخدم الحالي
    public function store(StoreContractRequest $request)
    {
        $contract = $this->contractService->createContract($request->validated());

        return response()->json([
            'message' => 'Contract created successfully',
            'contract' => $contract,
        ], 201);
    }
    // Attach user with role + wallet

    public function sendInvite(Request $request, $contractId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $contract = Contract::findOrFail($contractId);
        $contract = $this->contractService->updateContract($contract, ['status' => 'PendingApproval']);
        $contract->users()->attach($request->user_id, [
            'role' => 'service_provider',
            'user_address' => strtolower($request->user_address),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Invite sent']);
    }

    public function getInvites(Request $request)
    {
        $user = auth()->user();

        $invites = $user->contracts()->wherePivot('status', 'pending')->get();

        return response()->json(['invites' => $invites]);
    }

    public function respondInvite(Request $request, $contractId)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $user = auth()->user();

        $user->contracts()->updateExistingPivot($contractId, [
            'status' => $request->status,
        ]);

        // Optional: delete contract if rejected by provider
        if ($request->status === 'rejected') {
            $contract = Contract::find($contractId);
            if ($contract) $contract->update(['status' => 'Rejected']);
        }

        return response()->json(['message' => "Invite {$request->status}"]);
    }

    /**
     * get all my contracts
     */
    public function myContracts()
    {
        $user = auth()->user();

        $contracts = $user->contracts()
            // ->with(['users', 'clauses']) // eager load relations
            ->orderBy('contracts.created_at', 'desc')
            ->get();

        return response()->json(['contracts' => $contracts]);
    }

    public function attachUser(Request $request) {}



    public function exportContractPdf($id)
    {
        $contract = Contract::with('clauses', 'users')->findOrFail($id);

        // Generate PDF
        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));

        // File name
        $fileName = "contract-{$contract->id}.pdf";

        // Save PDF into storage/app/public/contracts
        $filePath = "contracts/{$fileName}";
        Storage::disk('public')->put($filePath, $pdf->output());

        // Save path in DB
        $contract->update([
            'pdf_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Contract PDF generated & saved successfully',
            'download_url' => asset("storage/{$filePath}")
        ]);
    }
}
