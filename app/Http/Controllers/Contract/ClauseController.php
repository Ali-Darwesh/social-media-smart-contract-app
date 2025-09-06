<?php

namespace App\Http\Controllers\Contract;

use App\Events\ClauseSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreClauseRequest;
use App\Http\Requests\Contract\UpdateClaseRequest;
use App\Models\Clause;
use App\Models\Contract;
use App\Services\Contract\ClauseService;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;

class ClauseController extends Controller
{
    protected $clauseService;
    function __construct(ClauseService $clauseService)
    {
        $this->clauseService = $clauseService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id, StoreClauseRequest $request)
    {
        $data = $request->validated();
        $clause = $this->clauseService->createClause($id, $data);
        // broadcast(new ClauseSent($clause['clause']))->toOthers();
        return response()->json([
            'message' => $clause['message'],
            'clause' => $clause['clause'],
        ], $clause['status'] ?? 201);
    }

    public function getClauses($contractId)
    {
        $contract = Contract::with(['clauses'])->findOrFail($contractId);

        return response()->json([
            'contract_id' => $contract->id,
            'Clauses' => $contract->clauses
        ]);
    }
    public function getApprovedClauses($contractId)
    {
        $contract = Contract::with('approvedClauses')->findOrFail($contractId);

        return response()->json([
            'approved_clauses' => $contract->approvedClauses
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClaseRequest $request, Clause $clause)
    {
        $data = $request->validated();
        $resulte = $this->clauseService->updateClause($clause, $data);
        return response()->json([
            'message' => $resulte['message'],
            'clause' => $resulte['clause'],
        ], $resulte['status'] ?? 201);
    }
    public function accepteClause($id)
    {
        $clause = Clause::findOrFail($id);

        $resulte = $this->clauseService->updateClause(
            $clause,
            [
                'approved_by_a' => 1,
                'approved_by_b' => 1,
            ]
        );
        return response()->json([
            'message' => $resulte['message'],
            'clause' => $resulte['clause'],
        ], $resulte['status'] ?? 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clause $clause)
    {
        $clause = $clause->delete();
        return response()->json([
            'message' => 'clause deleted successfuly',
            'clause' => $clause
        ], 200);
    }
}
