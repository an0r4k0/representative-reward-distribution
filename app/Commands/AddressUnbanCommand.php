<?php

namespace App\Commands;

use App\Models\Banned;
use LaravelZero\Framework\Commands\Command;

class AddressUnbanCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'address:unban {address}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Unban address';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = $this->argument('address');
        $this->deleteBanned($address);
    }

    /**
     * Unban address to send payouts
     */
    protected function deleteBanned($address)
    {
        Banned::byAddress($address)->delete();
    }
}
