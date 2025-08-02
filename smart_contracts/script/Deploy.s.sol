// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.20;

import "forge-std/Script.sol";
import "../src/ServiceAgreement.sol";

contract Deploy is Script {
    function run() external {
        address provider = 0x70997970C51812dc3A010C7d01b50e0d17dc79C8;

        uint256 totalAmount = 1 ether;

        vm.startBroadcast();

        // إرسال 1 ether مع تمرير القيمة المطلوبة
        new ServiceAgreement{value: totalAmount}(provider, totalAmount);

        vm.stopBroadcast();
    }
}
