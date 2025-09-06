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
            'contract_address' => $data['contract_address'] ?? null,
            'client' => $data['client'] ?? null,
            'serviceProvider' => $data['serviceProvider'] ?? null,
            'totalAmount' => $data['totalAmount'] ?? null,
            'status' => $data['status'] ?? $contract->status,

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
    public function updateUserAddresses(Contract $contract, string $client, string $serviceProvider): void
    {
        // Update client
        $contract->users()
            ->wherePivot('role', 'client')
            ->updateExistingPivot(
                $contract->users()->wherePivot('role', 'client')->first()->id,
                ['user_address' => strtolower($client)]
            );

        // Update service provider
        $contract->users()
            ->wherePivot('role', 'service_provider')
            ->updateExistingPivot(
                $contract->users()->wherePivot('role', 'service_provider')->first()->id,
                ['user_address' => strtolower($serviceProvider)]
            );
    }
}
