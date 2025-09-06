<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\Clause;
use Exception;

class ClauseService
{
    public function createClause(int $id, array $data)
    {
        $contract = Contract::findOrFail($id);
        try {
            $clause = $contract->clauses()->create([
                'text' => $data['text'],
                'proposer_address' => strtolower($data['proposer_address']),
                'approved_by_a' => strtolower($data['proposer_address']) === strtolower($contract->client),
                'approved_by_b' => strtolower($data['proposer_address']) === strtolower($contract->serviceProvider),
                'executed' => false,
                'amount_usd' => $data['amount_usd'],
                'due_date' => $data['due_date'],
            ]);

            return [
                'message' => 'Clause created successfully',
                'clause' => $clause,
            ];
        } catch (Exception $e) {
            return [
                'message' => $e,
                'clause' => null,
                'status' => 500
            ];
        }
    }

    public function updateClause(Clause $clause, array $data): array
    {
        try {
            $clause->update($data);

            return [
                'message' => 'Clause updated successfully',
                'clause' => $clause,
            ];
        } catch (Exception $e) {
            return [
                'message' => $e,
                'clause' => $clause,
                'status' => 500
            ];
        }
    }

    public function deleteClause(Clause $clause): array
    {
        $clause->delete();

        return [
            'message' => 'Clause deleted successfully',
        ];
    }
}
