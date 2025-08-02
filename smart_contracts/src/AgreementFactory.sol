// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

import "./ServiceAgreement.sol";

contract AgreementFactory {
    address[] public allAgreements;

    event AgreementCreated(address indexed agreementAddress, address indexed client, address indexed provider);

    function createAgreement(address _provider, uint256 _totalAmount) external payable returns (address) {
        require(msg.sender != _provider, "Client and provider must differ");
        require(msg.value == _totalAmount, "Incorrect amount sent");

        ServiceAgreement agreement = (new ServiceAgreement){value: msg.value}(_provider, _totalAmount);

        allAgreements.push(address(agreement));

        emit AgreementCreated(address(agreement), msg.sender, _provider);

        return address(agreement);
    }

    function getAllAgreements() external view returns (address[] memory) {
        return allAgreements;
    }

    function getAgreementsCount() external view returns (uint256) {
        return allAgreements.length;
    }
}
