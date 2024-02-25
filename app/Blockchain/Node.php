<?php

namespace App\Blockchain;

use GuzzleHttp\Client;

class Node
{
    /**
     * Node RPC address
     */
    protected $rpc;

    public function __construct($rpc)
    {
        $this->rpc = $rpc;
    }

    /**
     * Get the delegators of an account
     */
    public function delegators($address)
    {
        $response = $this->client()->post('/', [
            'json' => [
                'action' => 'delegators',
                'account' => $address
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        return !empty($body['delegators'])
            ? collect($body['delegators'])
                ->map(fn ($value, $key) => [
                    'address' => $key,
                    'balance' => $value
                ])
                ->values()
            : collect([]);
    }

    /**
     * Get the balance of an account
     */
    public function accountBalance($address)
    {
        $response = $this->client()->post('/', [
            'json' => [
                'action' => 'account_balance',
                'account' => $address
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Send transaction
     */
    public function send($walletId, $source, $destination, $amount, $id)
    {
        $response = $this->client()->post('/', [
            'json' => [
                'action' => 'send',
                'wallet' => $walletId,
                'source' => $source,
                'destination' => $destination,
                'amount' => $amount,
                'id' => $id
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the client
     */
    protected function client()
    {
        return new Client([
            'base_uri' => $this->rpc
        ]);
    }
}
