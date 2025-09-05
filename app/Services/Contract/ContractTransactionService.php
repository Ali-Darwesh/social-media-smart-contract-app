<?php

namespace App\Services\Contract;

use Web3\Contract;
use Web3\Web3;
use Exception;
use Illuminate\Support\Facades\Http;

class ContractTransactionService
{
    protected $web3;
    protected $contract;
    protected $abi;
    protected $bytecode;

    public function __construct()
    {
        $this->web3 = new Web3('http://192.168.1.8:8545'); // Anvil
        $this->abi = file_get_contents(storage_path('app/contracts/ServiceAgreement.abi.json'));
        $this->bytecode = file_get_contents(storage_path('app/contracts/ServiceAgreement.bin'));

        $this->contract = new Contract($this->web3->provider, $this->abi);
    }

    /////////////////////////
    /////  Deploy
    /////////////////////////
    public function deployContract(string $client, string $serviceProvider, string $totalAmountWei, string $totalAmountWeiHex): array
    {
        $deploymentTx = null;

        $this->contract->bytecode($this->bytecode)->new(
            $serviceProvider,
            $totalAmountWei,
            [
                'from' => $client,
                'gas' => '0x5B8D80',
                'value' => $totalAmountWeiHex
            ],
            function ($err, $tx) use (&$deploymentTx) {
                if ($err !== null) {
                    throw new Exception($err->getMessage());
                }
                $deploymentTx = $tx;
            }
        );

        // Fetch receipt
        $receipt = null;
        $this->web3->eth->getTransactionReceipt($deploymentTx, function ($err, $txReceipt) use (&$receipt) {
            if ($err !== null) {
                throw new Exception("Error fetching receipt: " . $err->getMessage());
            }
            $receipt = $txReceipt;
        });

        if (!$receipt) {
            throw new Exception("Transaction not yet mined. Try again later.");
        }

        return [
            'message' => 'Contract deployed successfully',
            'txHash' => $deploymentTx,
            'contractAddress' => $receipt->contractAddress ?? null,
        ];
    }


    /////////////////////////
    /**   Add Clause   Two kinds
     *          1.addApprovedClause  
     *          2.addClause 
     */
    /////////////////////////

    /////////////////////////
    /////  1.  AddApprovedClause
    /////////////////////////
    public function addApprovedClause(string $contractAddress, string $text, string $amountWei, string $from)
    {
        try {
            $txHash = null;
            $contract = $this->contract->at($contractAddress);

            $contract->send(
                'addApprovedClause',
                $text,
                $amountWei,
                ['from' => $from, 'gas' => '0x5B8D80'],
                function ($err, $tx) use (&$txHash) {
                    if ($err !== null) throw new Exception($err->getMessage());
                    $txHash = $tx;
                }
            );
            $receipt = null;
            $this->web3->eth->getTransactionReceipt($txHash, function ($err, $txReceipt) use (&$receipt) {
                if ($err !== null) {
                    throw new Exception("Error fetching receipt: " . $err->getMessage());
                }
                $receipt = $txReceipt;
            });

            if (!$receipt) {
                throw new Exception("Transaction not yet mined. Try again later.");
            }
            return [
                'message' => 'Clause added successfully',
                'txHash' => $txHash,
                'receipt' => $receipt
            ];
        } catch (Exception $e) {
            return [
                'message' => $e,
                'txHash' => null,
                'status' => 500
            ];
        }
    }
    /////////////////////////
    /////  2.  AddClause 
    /////////////////////////
    public function addClause(string $contractAddress, string $text, string $amountWei, string $from)
    {
        try {
            $txHash = null;
            $contract = $this->contract->at($contractAddress);

            $contract->send(
                'addClause',
                $text,
                $amountWei,
                ['from' => $from, 'gas' => '0x5B8D80'],
                function ($err, $tx) use (&$txHash) {
                    if ($err !== null) throw new Exception($err->getMessage());
                    $txHash = $tx;
                }
            );
            $receipt = null;
            $this->web3->eth->getTransactionReceipt($txHash, function ($err, $txReceipt) use (&$receipt) {
                if ($err !== null) {
                    throw new Exception("Error fetching receipt: " . $err->getMessage());
                }
                $receipt = $txReceipt;
            });

            if (!$receipt) {
                throw new Exception("Transaction not yet mined. Try again later.");
            }
            return [
                'message' => 'Clause added successfully',
                'txHash' => $txHash,
                'receipt' => $receipt
            ];
        } catch (Exception $e) {
            return [
                'message' => $e,
                'txHash' => null,
                'status' => 500
            ];
        }
    }

    /////////////////////////
    /////  Approve Clause
    /////////////////////////
    public function approveClause(string $contractAddress, int $index, string $from): array
    {
        $txHash = null;
        $this->contract->at($contractAddress)->send(
            'approveClause',
            $index,
            ['from' => $from, 'gas' => '0x5B8D80'],
            function ($err, $tx) use (&$txHash) {
                if ($err !== null) throw new Exception($err->getMessage());
                $txHash = $tx;
            }
        );

        return [
            'message' => 'Clause approved successfully',
            'txHash' => $txHash
        ];
    }

    /////////////////////////
    /////  Read Clause
    /////////////////////////
    public function getClause(string $contractAddress, int $index): array
    {
        $clause = [];
        $this->contract->at($contractAddress)->call(
            'getClause',
            $index,
            function ($err, $res) use (&$clause) {
                if ($err !== null) throw new Exception($err->getMessage());
                $clause = [
                    'text' => $res['text'],
                    'proposer' => $res['proposer'],
                    'approvedByA' => $res['approvedByA'],
                    'approvedByB' => $res['approvedByB'],
                    'executed' => $res['executed'],
                    'amount' => $res['amount']->toString(),
                ];
            }
        );

        return [
            'message' => 'Clause fetched successfully',
            'clause' => $clause,
        ];
    }
}
