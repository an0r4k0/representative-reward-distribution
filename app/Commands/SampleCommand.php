<?php

namespace App\Commands;

use App\Blockchain\Address;
use App\Blockchain\Node;
use App\Models\Delegator;
use App\Models\Sample;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SampleCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sample';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Sample tribe delegators';

    /**
     * Tribe
     */
    protected $tribe;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tribe = new Address(
            config('tribe.tribe.address'),
            new Node(config('tribe.rpc.address')),
        );

        parent::__construct();
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        $interval = config('tribe.tribe.sampling_interval');
        $schedule->command(static::class)->cron("*/{$interval} * * * *");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tribe->delegators()
            ->tap(function ($delegators) {
                $delegators
                    ->pluck('address')
                    ->tap(fn ($delegators) => $this->setUneligibleDelegators($delegators));
            })
            ->each(fn ($delegator) => $this->updateDelegator($delegator['address']))
            ->each(fn ($delegator) => $this->addSample($delegator['address'], $delegator['balance']));
    }

    /**
     * Remove old delegators
     */
    protected function setUneligibleDelegators($addresses)
    {
        Delegator::whereNotIn('address', $addresses)
            ->update([
                'delegated_at' => null
            ]);
    }

    /**
     * Store or update a delegator
     */
    protected function updateDelegator($address)
    {
        $delegator = Delegator::firstOrNew(['address' => $address]);

        // If the delegated_at date is not set set it as now
        // because it is a new delegator
        if (!$delegator->delegated_at) {
            $delegator->delegated_at = now();
        }

        $delegator->save();
    }

    /**
     * Store a sample
     */
    protected function addSample($address, $balance)
    {
        $sample = new Sample([
            'address' => $address,
            'balance' => $balance
        ]);
        $sample->save();
    }
}
