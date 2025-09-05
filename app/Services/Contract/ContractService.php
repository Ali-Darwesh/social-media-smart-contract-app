<?php

namespace App\Services\Contract;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Exception;

class ContractService
{
    public function createContract($data)
    {
        $contract = Contract::create(
            $data
        );
        $contract->users()->attach($data['user_id'], [
            'status' => 'accepted',
        ]);
        return [
            'message' => 'Contract created successfully',
            'contract' => $contract,
        ];
    }

    public function updateContract(Contract $contract, array $data)
    {
        $contract->update([
            'contract_address' => $data['contract_address'],
            'client' => $data['client'],
            'serviceProvider' => $data['serviceProvider'],
            'totalAmount' => $data['totalAmount']


        ]);
        return $contract;
    }

    public function deleteContract(Contract $contract)
    {
        $contract->delete();
    }

    public function attachUser(Contract $contract, array $data)
    {
        $attachUsers = $contract->users()->attach(
            $data
        );
        return ['message' => 'attach users success'];
    }
}
