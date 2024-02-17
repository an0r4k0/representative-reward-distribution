<?php

namespace App\Commands;

use App\Models\Banned;
use LaravelZero\Framework\Commands\Command;

class AddressBanCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'address:ban {address}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Ban address from payouts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = $this->argument('address');
        $this->banAddress($address);
    }

    /**
     * Ban address from payouts
     */
    protected function banAddress($address)
    {
        Banned::firstOrNew(['address' => $address])->save();
    }
}
