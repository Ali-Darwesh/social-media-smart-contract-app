// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract SmartContractAgreement {
    enum Status { Pending, Signed, Rejected }

    struct Contract {
        uint id;
        string title;
        string details;
        address creator;
        address[] participants;
        mapping(address => bool) signed;
        uint signatures;
        Status status;
    }

    uint public nextId;
    mapping(uint => Contract) private contracts;

    event ContractCreated(uint id, address creator);
    event Signed(uint id, address signer);
    event Rejected(uint id, address rejector);
    event StatusChanged(uint id, Status status);

    function createContract(string memory _title, string memory _details, address[] memory _participants) public {
        require(_participants.length > 0, "At least one participant required");

        Contract storage c = contracts[nextId];
        c.id = nextId;
        c.title = _title;
        c.details = _details;
        c.creator = msg.sender;
        c.participants = _participants;
        c.status = Status.Pending;

        emit ContractCreated(nextId, msg.sender);
        nextId++;
    }

    function signContract(uint _id) public {
        Contract storage c = contracts[_id];
        require(c.status == Status.Pending, "Contract is not pending");

        bool isParticipant = false;
        for (uint i = 0; i < c.participants.length; i++) {
            if (c.participants[i] == msg.sender) {
                isParticipant = true;
                break;
            }
        }
        require(isParticipant, "You are not a participant");
        require(!c.signed[msg.sender], "Already signed");

        c.signed[msg.sender] = true;
        c.signatures++;

        emit Signed(_id, msg.sender);

        if (c.signatures == c.participants.length) {
            c.status = Status.Signed;
            emit StatusChanged(_id, Status.Signed);
        }
    }

    function rejectContract(uint _id) public {
        Contract storage c = contracts[_id];
        require(c.status == Status.Pending, "Contract is not pending");

        bool isParticipant = false;
        for (uint i = 0; i < c.participants.length; i++) {
            if (c.participants[i] == msg.sender) {
                isParticipant = true;
                break;
            }
        }
        require(isParticipant, "You are not a participant");

        c.status = Status.Rejected;

        emit Rejected(_id, msg.sender);
        emit StatusChanged(_id, Status.Rejected);
    }

    function getContract(uint _id) public view returns (
        string memory, string memory, address, address[] memory, Status, uint
    ) {
        Contract storage c = contracts[_id];
        return (
            c.title,
            c.details,
            c.creator,
            c.participants,
            c.status,
            c.signatures
        );
    }

    function getSignaturesStatus(uint _id) public view returns (address[] memory, bool[] memory) {
        Contract storage c = contracts[_id];
        uint length = c.participants.length;

        bool[] memory signedStatus = new bool[](length);

        for (uint i = 0; i < length; i++) {
            signedStatus[i] = c.signed[c.participants[i]];
        }

        return (c.participants, signedStatus);
    }
}
