<?php

namespace App\Commands;

use App\Blockchain\Node;
use App\Models\Payout;
use Illuminate\Console\Scheduling\Schedule;
use \Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class ProcessCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'process';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Process pending payouts';

    /**
     * Node
     */
    protected $node;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->node = new Node(config('settings.node.rpc'));

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Payout::pending()
            ->orderByCreation()
            ->chunk(100, fn ($payouts) => $payouts->each(fn ($payout) => $this->process($payout->fresh())));
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->withoutOverlapping()->everyMinute();
    }

    /**
     * Get next payout to process
     */
    protected function getNextPayoutToProcess()
    {
        return Payout::pending()->orderByCreation()->first();
    }

    /**
     * Process payout
     */
    protected function process(Payout $payout)
    {
        // Let's create a unique tx_id and save it
        if (!$payout->tx_id) {
            $txId = Str::uuid();
            $payout->tx_id = $txId;
            $payout->save();
        }

        // Let's send the amount
        $this->node->send(
            config('settings.node.wallet_id'),
            config('settings.node.address'),
            $payout->address,
            $payout->amount,
            $payout->tx_id
        );

        // Let's set that the payout has been processed
        $payout->paid_at = now();
        $payout->save();
    }
}
