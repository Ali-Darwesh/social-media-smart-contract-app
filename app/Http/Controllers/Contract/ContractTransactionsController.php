<?php

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\Contract\ClauseService;
use App\Services\Contract\ContractService;
use App\Services\Contract\ContractTransactionService;
use App\Services\Web3Service;
use Illuminate\Http\Request;
use kornrunner\Keccak;
use Elliptic\EC;
use Elliptic\Utils;
use Lcobucci\JWT\Signer\Ecdsa;
use Web3\Contract as Web3Contract;
use Web3\Web3;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class ContractTransactionsController extends Controller
{
    protected $web3;
    protected $contract;
    protected $abi;
    protected $bytecode;
    protected $contractService;
    protected $contractTransactionService;
    protected $clauseService;

    public function __construct(ContractService $contractService, ContractTransactionService $contractTransactionService, ClauseService $clauseService)
    {
        $this->web3 = new Web3('http://192.168.1.8:8545'); // Anvil
        $this->abi = file_get_contents(storage_path('app/contracts/ServiceAgreement.abi.json'));
        $this->bytecode = file_get_contents(storage_path('app/contracts/ServiceAgreement.bin'));

        $this->contract = new Web3Contract($this->web3->provider, $this->abi);
        $this->contractService = $contractService;
        $this->clauseService = $clauseService;
        $this->contractTransactionService = $contractTransactionService;
    }

    ///////////////////////////////////////////
    /////                  DEPLOY THE CONTRACT
    ///////////////////////////////////////////

    public function deploy(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'signature' => 'required|string',
            'client' => 'required|string',
            'serviceProvider' => 'required|string',
            'totalAmount' => 'required|numeric'
        ]);

        $message = $request->message;
        $signature = $request->signature;
        $client = $request->client;
        $serviceProvider = $request->serviceProvider;
        $totalAmount = $request->totalAmount; // in ETH
        $totalAmount_in_wei = $this->convertUsdToWei($totalAmount);
        $totalAmount_in_wei_hex = $this->bigDecToHex($totalAmount_in_wei);
        echo ($totalAmount_in_wei_hex);
        try {
            // 2. Verify the signature -> recover signer
            $recovered = $this->verifySignature($message, $signature);

            if (strtolower($recovered) !== strtolower($client)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }



            // 4. Compute totalAmount in Wei
            $result = $this->contractTransactionService->deployContract(
                $client,
                $serviceProvider,
                $totalAmount_in_wei,
                $totalAmount_in_wei_hex
            );
            $contract = Contract::findOrFail($id);
            $contractDB = $this->contractService->updateContract($contract, [
                'contract_address' => $result['contractAddress'],
                'client' => $client,
                'serviceProvider' => $serviceProvider,
                'totalAmount' => $totalAmount
            ]);
            $clauses = Contract::with('approvedClauses')->findOrFail($id);
            foreach ($clauses->approvedClauses as $clause) {
                $this->contractTransactionService->addApprovedClause(
                    $result['contractAddress'],
                    $clause['text'],
                    $this->convertUsdToWei($clause['amount_usd']),
                    $clause['proposer_address'],


                );
            }
            return response()->json([
                'status' => 'success',
                'message' => $result["message"],
                'txHash' => $result['txHash'],
                'contractAddress' => $result['contractAddress'],
                'contractDB' => $contractDB,
                'attachUsers' => $contractDB['attachUsers'],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recover signer address from message + signature
     */


    function verifySignature($message, $signature)
    {
        $msglen = strlen($message);
        $prefix = "\x19Ethereum Signed Message:\n{$msglen}{$message}";
        $hash   = Keccak::hash($prefix, 256);

        $r = substr($signature, 2, 64);
        $s = substr($signature, 66, 64);
        $v = hexdec(substr($signature, 130, 2));

        if ($v < 27) {
            $v += 27;
        }
        $recid = $v - 27;

        $ec = new EC('secp256k1');
        $pubkey = $ec->recoverPubKey($hash, ['r' => $r, 's' => $s], $recid);
        $pubkeyHex = $pubkey->encode('hex');

        // Ethereum address = last 20 bytes of keccak256(pubkey[1:])
        $addr = '0x' . substr(Keccak::hash(substr(hex2bin($pubkeyHex), 1), 256), 24);

        return strtolower($addr);
    }

    private function bigDecToHex($decimal)
    {
        $hex = '';
        while (bccomp($decimal, '0') > 0) {
            $rem = bcmod($decimal, '16');
            $decimal = bcdiv($decimal, '16', 0);
            $hex = dechex($rem) . $hex;
        }
        return '0x' . ($hex === '' ? '0' : $hex);
    }


    ///////////////////////////////////////////
    /////  convertUsdToWei
    ///////////////////////////////////////////

    public function convertUsdToWei($usd)
    {


        // 1. Get ETH price
        $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => 'ethereum',
            'vs_currencies' => 'usd'
        ]);

        $ethPriceUsd = $response->json()['ethereum']['usd'];

        // 2. Convert USD → ETH
        $ethAmount = $usd / $ethPriceUsd;

        // 3. Convert ETH → Wei
        $weiAmount = bcmul((string)$ethAmount, bcpow('10', '18'));

        return $weiAmount;
    }

    public function convertWeiToUsd($wei)
    {
        // 1. Get ETH price in USD
        $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => 'ethereum',
            'vs_currencies' => 'usd'
        ]);

        $ethPriceUsd = $response->json()['ethereum']['usd'];

        // 2. Convert Wei → ETH
        $ethAmount = bcdiv((string)$wei, bcpow('10', '18'), 18);

        // 3. Convert ETH → USD
        $usdAmount = bcmul($ethAmount, (string)$ethPriceUsd, 0);

        return $usdAmount; // string with 2 decimal precision
    }
    /**
     * convert wei to Hex
     */





    ///////////////////////////////////////////
    /////  INTERACTING WITH CONTRACT FUNCTIONS
    ///////////////////////////////////////////
    public function addClause(Request $request, $id)
    {

        $request->validate([
            'message' => 'required|string',
            'signature' => 'required|string',
            'user_address' => 'required|string',
            'text' => 'required|string',
            'amount' => 'required|numeric'
        ]);
        $message = $request->message;
        $signature = $request->signature;
        $user_address = $request->input('user_address');

        // 2. Verify the signature -> recover signer
        $recovered = $this->verifySignature($message, $signature);

        if (strtolower($recovered) !== strtolower($user_address)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        $text = $request->input('text');
        $amount = $request->input('amount'); // in Wei
        $contract_address = $request->input('contract_address');
        $amount_in_wei = $this->convertUsdToWei($amount);
        $result = $this->contractTransactionService->addClause(
            $contract_address,
            $text,
            $amount_in_wei,
            $user_address
        );

        $Clause_DB = $this->clauseService->createClause($id, [

            'text' => $text,
            'proposer' => $user_address,
            'amount' => $amount,
        ]);
        return response()->json([
            'message' => $result['message'],
            'data' => $result["txHash"],
            'receipt' => $result["receipt"],
            'Clause_DB' => $Clause_DB

        ], $result['status'] ?? 201);
    }
    public function addApprovedClause(Request $request, $id)
    {

        $request->validate([
            'message' => 'required|string',
            'signature' => 'required|string',
            'user_address' => 'required|string',
            'text' => 'required|string',
            'amount' => 'required|numeric'
        ]);
        $message = $request->message;
        $signature = $request->signature;
        $user_address = $request->input('user_address');

        // 2. Verify the signature -> recover signer
        $recovered = $this->verifySignature($message, $signature);

        if (strtolower($recovered) !== strtolower($user_address)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        $text = $request->input('text');
        $amount = $request->input('amount'); // in Wei
        $contract_address = $request->input('contract_address');
        $amount_in_wei = $this->convertUsdToWei($amount);
        $result = $this->contractTransactionService->addClause(
            $contract_address,
            $text,
            $amount_in_wei,
            $user_address
        );

        $Clause_DB = $this->clauseService->createClause($id, [

            'text' => $text,
            'proposer' => $user_address,
            'amount' => $amount,
        ]);
        return response()->json([
            'message' => $result['message'],
            'data' => $result["txHash"],
            'receipt' => $result["receipt"],
            'Clause_DB' => $Clause_DB

        ], $result['status'] ?? 201);
    }

    public function approveClause(Request $request)
    {
        $index = $request->input('index');
        $contract_address = $request->input('contract_address');
        $user_address = $request->input('user_address');

        $txHash = null;
        $this->contract->at($contract_address)
            ->send(
                'approveClause',
                intval($index),
                ['from' => $user_address, 'gas' => '0x5B8D80'],
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
        return response()->json(['txHash' => $txHash, "receipt" => $receipt]);
    }



    public function executeClause(Request $request)
    {
        $index = $request->input('index');
        $contract_address = $request->input('contract_address');
        $client = $request->input('client');
        $txHash = null;
        echo ($contract_address);

        $this->contract->at($request->input('contract_address'))
            ->send(
                'executeClause',
                intval($index),
                ['from' => $client, 'gas' => '0x5B8D80'],
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
        return response()->json(['txHash' => $txHash, "receipt" => $receipt]);
    }

    public function rejectContract(Request $request)
    {
        $contract_address = $request->input('contract_address');
        $user_address = $request->input('user_address');
        $txHash = null;
        $this->contract->at($contract_address)
            ->send(
                'rejectContract',

                ['from' => $user_address, 'gas' => '0x5B8D80'],
                function ($err, $tx) use (&$txHash) {
                    if ($err !== null) throw new Exception($err->getMessage());
                    $txHash = $tx;
                }
            );

        return response()->json(['txHash' => $txHash]);
    }


    //////////////////////////
    //////////   Read
    /////////////////////////

    public function getClausesCount(Request $request)
    {
        $count = 0;
        $this->contract->at($request->input("contract_address"))
            ->call('getClausesCount', [], function ($err, $res) use (&$count) {
                if ($err !== null) throw new Exception($err->getMessage());
                $count = intval($res[0]->toString());
            });

        return response()->json(['count' => $count]);
    }
    public function getClause(Request $request)
    {
        $index = $request->input("index");
        $clause = [];
        $this->contract->at($request->input("contract_address"))
            ->call('getClause', $index, function ($err, $res) use (&$clause) {
                if ($err !== null) throw new Exception($err->getMessage());
                $clause = [
                    'text' => $res['text'],
                    'proposer' => $res['proposer'],
                    'approvedByA' => $res['approvedByA'],
                    'approvedByB' => $res['approvedByB'],
                    'executed' => $res['executed'],
                    'amount' => $this->convertWeiToUsd($res['amount']->value) . " $",
                ];
            });

        return response()->json(['clause' => $clause]);
    }

    public function getStatus(Request $request)
    {
        $result = null;
        $this->contract->at($request->input("contract_address"))
            ->call('getStatus', [], function ($err, $res) use (&$result) {
                if ($err !== null) throw new Exception($err->getMessage());
                $result = $res[0];
            });

        return response()->json(['status' => $result]);
    }
}
