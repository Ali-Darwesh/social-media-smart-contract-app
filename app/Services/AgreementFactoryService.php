<?php 
// app/Services/AgreementFactoryService.php
namespace App\Services;

use Web3\Web3;
use Web3\Contract;
use Illuminate\Support\Facades\Log;

class AgreementFactoryService
{
    protected $web3;
    protected $contract;
    protected $factoryAddress;

    public function __construct()
    {
        $this->web3 = new Web3('http://127.0.0.1:8545');
        $this->factoryAddress = '0xYourFactoryContractAddressHere';

        $abi = json_decode(file_get_contents(storage_path('abi/AgreementFactory.json')), true);
        $this->contract = new Contract($this->web3->provider, $abi);
        $this->contract->at($this->factoryAddress);
    }

    public function getAllAgreements()
    {
        $result = null;
        $error = null;

        $this->contract->call('getAllAgreements', [], function ($err, $res) use (&$result, &$error) {
            if ($err !== null) {
                $error = $err;
                return;
            }
            $result = $res[0]; // array of addresses
        });

        if ($error) {
            Log::error('getAllAgreements error: ' . $error->getMessage());
            return [];
        }

        return $result;
    }
}
