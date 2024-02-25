# Representative Reward Distribution

A simple tribe reward system: it will sample all the tribe delegators balances and reward them fairly.

## Installation 

Run the docker image with the following command:
```bash
docker run --restart=unless-stopped -d -v $(pwd)/rrd:/root/rrd: -e MANAGEMENT_ADDRESS=... -e MANAGEMENT_PERCENTAGE=15 -e DELEGATOR_MIN_BALANCE=1 -e DELEGATOR_SAMPLING_INTERVAL=5 -e WALLET_ID=... -e ADDRESS=... -e NODE_RPC="http://localhost:7076" -e TIMEZONE=UTC representative-reward-distribution
```

Where
- `MANAGEMENT_ADDRESS` is the address of the wallet that will be used to send the management fee
- `MANAGEMENT_PERCENTAGE` is the percentage of the rewards that will be sent to the management address
- `DELEGATOR_MIN_BALANCE` is the minimum balance that a delegator must have to be rewarded
- `DELEGATOR_SAMPLING_INTERVAL` is the interval in minutes between each balance sampling
- `WALLET_ID` is the wallet ID of your representative node
- `ADDRESS` is the address of your representative node
- `NODE_RPC` is the RPC address of your representative node
- `TIMEZONE` is the timezone of your server

The wallet ID can be recovered from the `paw_node` executable using:
```bash
nano_node --wallet_decrypt_unsafe --wallet YOUR_WALLET_ID
```

Where `YOUR_WALLET_ID` is the wallet ID of your representative node.

All set! Delegators will be rewarded after about 48h.

## Advanced configuration

Some "advanced" config like banning and whitelisting are also available. To access them simply run a shell inside the running container:

```bash
docker exec -it CONTAINER_ID sh
```

Where `CONTAINER_ID` is the ID of the running container. You can find it using:
```bash
docker ps
```

Once inside the container, you can obtain the list of available commands using:
```bash
php application
```
