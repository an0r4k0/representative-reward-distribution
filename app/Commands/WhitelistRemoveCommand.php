<?php

namespace App\Commands;

use App\Models\Whitelist;
use LaravelZero\Framework\Commands\Command;

class WhitelistRemoveCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'whitelist:remove {address}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove address from whitelist. If whitelist is not empty the only the addresses it contains will receive payouts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = $this->argument('address');
        $this->removeAddressFromWhitelist($address);
    }

    /**
     * Remove address from whitelist
     */
    protected function removeAddressFromWhitelist($address)
    {
        Whitelist::byAddress($address)->delete();
    }
}
