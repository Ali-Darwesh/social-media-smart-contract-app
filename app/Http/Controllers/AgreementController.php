<?php
// app/Http/Controllers/AgreementController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AgreementFactoryService;

class AgreementController extends Controller
{
    protected $factoryService;

    public function __construct(AgreementFactoryService $factoryService)
    {
        $this->factoryService = $factoryService;
    }

    public function list()
    {
        $agreements = $this->factoryService->getAllAgreements();

        return response()->json([
            'success' => true,
            'data' => $agreements
        ]);
    }

    // ❗ تركنا create فاضي لأنه لا يمكن تنفيذه بشكل آمن من Laravel
    public function create(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Direct smart contract interaction (createAgreement) must be done from frontend via wallet like MetaMask'
        ], 400);
    }
}
