<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Contract Document</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 13px;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }

        .meta {
            margin-bottom: 25px;
            font-size: 13px;
        }

        .meta div {
            margin: 4px 0;
        }

        .participants {
            margin-bottom: 20px;
        }

        .party {
            margin-bottom: 12px;
        }

        .party h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            text-decoration: underline;
        }

        .party p {
            margin: 2px 0;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 25px 0;
        }

        .clauses {
            margin-top: 20px;
        }

        .clauses h3 {
            margin-bottom: 15px;
            font-size: 15px;
            text-decoration: underline;
        }

        .clause {
            margin-bottom: 18px;
        }

        .clause-number {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .clause-details {
            margin-left: 20px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h2>Smart Contract Agreement</h2>
    </div>

    <!-- Contract Meta -->
    <div class="meta">
        <div><strong>Contract Address:</strong> {{ $contract->contract_address }}</div>
        <div><strong>Created At:</strong> {{ $contract->created_at->format('Y-m-d H:i') }}</div>
        <div><strong>Total Amount (USD):</strong> ${{ $contract->totalAmount }}</div>
    </div>

    <!-- Participants -->
    <div class="participants">
        @php
        $client = $contract->users->where('pivot.role', 'client')->first();
        $provider = $contract->users->where('pivot.role', 'service_provider')->first();
        @endphp

        <div class="party">
            <h4>Client</h4>
            <p><strong>Name:</strong> {{ $client->name ?? 'N/A' }}</p>
            <p><strong>Wallet Address:</strong> {{ $client->pivot->user_address ?? $contract->client }}</p>
        </div>

        <div class="party">
            <h4>Service Provider</h4>
            <p><strong>Name:</strong> {{ $provider->name ?? 'N/A' }}</p>
            <p><strong>Wallet Address:</strong> {{ $provider->pivot->user_address ?? $contract->serviceProvider }}</p>
        </div>
    </div>

    <!-- Divider -->
    <hr>

    <!-- Clauses -->
    <div class="clauses">
        <h3>Clauses</h3>
        @foreach($contract->clauses as $index => $clause)
        <div class="clause">
            <div class="clause-number">{{ $index + 1 }}. {{ $clause->text }}</div>
            <div class="clause-details">
                <div><strong>Amount:</strong> ${{ $clause->amount_usd }}</div>
                <div><strong>Due Date:</strong> {{ $clause->due_date ?? 'N/A' }}</div>
            </div>
        </div>
        @endforeach
    </div>
</body>

</html>