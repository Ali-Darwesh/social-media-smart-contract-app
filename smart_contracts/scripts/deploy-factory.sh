#!/bin/bash

# تأكد من وجود مفتاح خاص
if [ -z "$PRIVATE_KEY" ]; then
  echo "❌ PRIVATE_KEY not set. Run: export PRIVATE_KEY=your_key"
  exit 1
fi

RPC_URL="http://127.0.0.1:8545"

echo "Deploying AgreementFactory..."

DEPLOY_OUTPUT=$(forge script script/DeployFactory.s.sol \
  --rpc-url $RPC_URL \
  --private-key "$PRIVATE_KEY" \
  --broadcast)

FACTORY_ADDRESS=$(echo "$DEPLOY_OUTPUT" | grep "Factory deployed at:" | awk '{print 5}')

if [ -z "$FACTORY_ADDRESS" ]; then
  echo "❌ Could not extract factory address."
  exit 1
fi

mkdir -p deployments
echo "{\"address\": \"$FACTORY_ADDRESS\"}" > deployments/factory.json

echo "✅ Deployed at: $FACTORY_ADDRESS"
