// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.20;

import "forge-std/Script.sol";
import "../src/AgreementFactory.sol";
import "forge-std/console2.sol";

contract DeployFactory is Script {
    function run() external {
        vm.startBroadcast();

        AgreementFactory factory = new AgreementFactory();

        vm.stopBroadcast();

console2.log("Factory deployed at:");
console2.log(address(factory));
    }
}
