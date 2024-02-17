<?php

namespace App\Commands;

use App\Models\Whitelist;
use LaravelZero\Framework\Commands\Command;

class WhitelistAddCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'whitelist:add {address}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add address to whitelist. If whitelist is not empty the only the addresses it contains will receive payouts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = $this->argument('address');
        $this->addAddressToWhitelist($address);
    }

    /**
     * Add address to whitelist
     */
    protected function addAddressToWhitelist($address)
    {
        Whitelist::firstOrNew(['address' => $address])->save();
    }
}
