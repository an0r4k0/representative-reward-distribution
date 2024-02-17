<?php

namespace App\Blockchain;

class Address
{
    /**
     * Node
     */
    protected $node;

    /**
     * Address
     */
    protected $address;

    /**
     * Constructor
     */
    public function __construct($address, Node $node)
    {
        $this->address = $address;
        $this->node = $node;
    }

    /**
     * Get the address delegators
     */
    public function delegators()
    {
        return $this->node->delegators($this->address);
    }

    /**
     * Get the balance of the address
     */
    public function balance()
    {
        return $this->node->accountBalance($this->address);
    }
}
