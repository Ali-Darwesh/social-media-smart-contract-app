// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract ServiceAgreement is Ownable {
    enum Status {
        Draft,
        PendingApproval,
        Active,
        Rejected,
        Completed
    }

    struct Clause {
        string text;
        address proposer;
        bool approvedByA;
        bool approvedByB;
        bool executed;
        uint256 amount; // المبلغ المخصص للبند
    }

    address public client; // Party A
    address public serviceProvider; // Party B

    Clause[] public clauses;
    Status public status;

    uint256 public totalAmount;
    uint256 public amountReleased;
    uint256 public createdAt;
    uint256 public activatedAt;
    uint256 public completedAt;

    modifier onlyParticipants() {
        require(
            msg.sender == client || msg.sender == serviceProvider,
            "Not authorized"
        );
        _;
    }

    modifier onlyClient() {
        require(msg.sender == client, "Only client");
        _;
    }

    modifier onlyServiceProvider() {
        require(msg.sender == serviceProvider, "Only service provider");
        _;
    }

    event DebugDeploy(
        address deployer,
        address client,
        address serviceProvider,
        uint256 totalEth,
        uint256 msgValue
    );

    constructor(
        address _client,
        address _serviceProvider,
        uint256 _totalAmountInEth
    ) payable Ownable(_client) {
        emit DebugDeploy(
            msg.sender,
            _client,
            _serviceProvider,
            _totalAmountInEth,
            msg.value
        );

        require(_client != _serviceProvider, "Parties must differ");

        uint256 _totalAmountInWei = _totalAmountInEth * 1 ether;
        require(msg.value == _totalAmountInWei, "Must fund total amount");

        client = _client; // now passed from backend
        serviceProvider = _serviceProvider;
        totalAmount = _totalAmountInWei;
        status = Status.Draft;
        createdAt = block.timestamp;
    }

    function addClause(
        string memory _text,
        uint256 _amount
    ) external onlyParticipants {
        require(_amount > 0, "Clause amount must be > 0");

        uint256 allocated = 0;
        for (uint i = 0; i < clauses.length; i++) {
            allocated += clauses[i].amount;
        }

        require(allocated + _amount <= totalAmount, "Exceeds total budget");

        clauses.push(
            Clause({
                text: _text,
                proposer: msg.sender,
                approvedByA: msg.sender == client,
                approvedByB: msg.sender == serviceProvider,
                executed: false,
                amount: _amount
            })
        );

        status = Status.Draft;
    }

    function deleteClause(uint index) external onlyParticipants {
        require(index < clauses.length, "Invalid index");
        require(
            clauses[index].proposer == msg.sender,
            "Only proposer can delete"
        );

        // Remove clause by replacing it with last and popping
        clauses[index] = clauses[clauses.length - 1];
        clauses.pop();

        status = Status.Draft;
    }

    function editClause(
        uint index,
        string memory newText,
        uint256 newAmount
    ) external onlyParticipants {
        require(index < clauses.length, "Invalid clause index");

        Clause storage clause = clauses[index];
        require(clause.proposer == msg.sender, "Only proposer can edit");
        require(!clause.executed, "Cannot edit executed clause");

        uint256 allocated = 0;
        for (uint i = 0; i < clauses.length; i++) {
            if (i == index) continue;
            allocated += clauses[i].amount;
        }

        require(allocated + newAmount <= totalAmount, "Exceeds budget");

        clause.text = newText;
        clause.amount = newAmount;
        clause.approvedByA = msg.sender == client;
        clause.approvedByB = msg.sender == serviceProvider;

        status = Status.Draft;
    }

    function approveClause(uint index) external onlyParticipants {
        require(index < clauses.length, "Invalid clause index");

        Clause storage clause = clauses[index];
        if (msg.sender == client) {
            clause.approvedByA = true;
        } else {
            clause.approvedByB = true;
        }

        _checkIfAllApproved();
    }

    function executeClause(uint index) external onlyClient {
        require(index < clauses.length, "Invalid clause index");

        Clause storage clause = clauses[index];
        require(status == Status.Active, "Contract not active");
        require(
            clause.approvedByA && clause.approvedByB,
            "Clause not fully approved"
        );
        require(!clause.executed, "Already executed");

        clause.executed = true;
        amountReleased += clause.amount;

        (bool success, ) = serviceProvider.call{value: clause.amount}("");
        require(success, "Transfer failed");

        _checkIfAllExecuted();
    }

    function rejectContract() external onlyParticipants {
        require(
            status != Status.Rejected && status != Status.Completed,
            "Cannot reject now"
        );
        status = Status.Rejected;
        _refundClient();
    }

    function completeContract() external onlyParticipants {
        require(status == Status.Active, "Not active");
        require(_allClausesExecuted(), "Not all clauses executed");

        status = Status.Completed;
        completedAt = block.timestamp;
    }

    function _checkIfAllApproved() internal {
        if (clauses.length == 0) return;

        for (uint i = 0; i < clauses.length; i++) {
            if (!(clauses[i].approvedByA && clauses[i].approvedByB)) {
                status = Status.PendingApproval;
                return;
            }
        }

        status = Status.Active;
        activatedAt = block.timestamp;
    }

    function _checkIfAllExecuted() internal {
        if (_allClausesExecuted()) {
            status = Status.Completed;
            completedAt = block.timestamp;
        }
    }

    function _allClausesExecuted() internal view returns (bool) {
        for (uint i = 0; i < clauses.length; i++) {
            if (!clauses[i].executed) return false;
        }
        return true;
    }

    function _refundClient() internal {
        uint256 refund = address(this).balance;
        if (refund > 0) {
            (bool success, ) = client.call{value: refund}("");
            require(success, "Refund failed");
        }
    }

    function getClause(
        uint index
    )
        external
        view
        returns (
            string memory text,
            address proposer,
            bool approvedByA,
            bool approvedByB,
            bool executed,
            uint256 amount
        )
    {
        Clause storage c = clauses[index];
        return (
            c.text,
            c.proposer,
            c.approvedByA,
            c.approvedByB,
            c.executed,
            c.amount
        );
    }

    function getClausesCount() external view returns (uint) {
        return clauses.length;
    }

    function getStatus() external view returns (string memory) {
        if (status == Status.Draft) return "Draft";
        if (status == Status.PendingApproval) return "PendingApproval";
        if (status == Status.Active) return "Active";
        if (status == Status.Rejected) return "Rejected";
        if (status == Status.Completed) return "Completed";
        return "";
    }
}
