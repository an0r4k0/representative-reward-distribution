<?php

namespace App\Commands;

use App\Blockchain\Address;
use App\Blockchain\Node;
use App\Models\Banned;
use App\Models\Delegator;
use App\Models\Payout;
use App\Models\Reward;
use App\Models\Sample;
use App\Models\Whitelist;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class DistributeCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'distribute';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Calcolate payouts for this reward';

    /**
     * Node
     */
    protected $node;

    /**
     * Representative
     */
    protected $representative;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->node = new Node(config('settings.node.rpc'));
        $this->representative = new Address(config('settings.node.address'), $this->node);

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
        $schedule->command(static::class)->at('00:00');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $balance = $this->getDistibutableAmount();
        $this->distribute($balance);
    }

    /**
     * Distribute received rewards
     */
    protected function distribute($total)
    {
        $forManagementCost = $this->calculateManagementCostPayout($total);
        $forDelegators = gmp_sub($total, $forManagementCost);

        // Let's create the reward
        $reward = $this->createReward($forManagementCost, $forDelegators);

        $representativeManagementCostAddress = config('settings.management.address');
        if (gmp_cmp($forManagementCost, 0) > 0) {
            $this->addPayout($representativeManagementCostAddress, $forManagementCost);
        }

        // Let's distribute all remainging amount to distributors
        // If there aren't any then distribute all to managemnent
        $delegators = $this->getElegibleDelegators($reward);
        if ($delegators->isEmpty() && gmp_cmp($forDelegators, 0) > 0) {
            return $this->addPayout($representativeManagementCostAddress, $forDelegators);
        }

        if (gmp_cmp($forDelegators, 0) > 0) {
            $this->distributeToDelegators($delegators, $forDelegators);
        }
    }

    /**
     * Calculate tribe management payout
     */
    protected function calculateManagementCostPayout($amount)
    {
        // GMP only supports integers
        $managementPercentage = config('settings.management.percentage');
        $cost = gmp_mul($amount, $managementPercentage);
        $cost = gmp_div($cost, 100);
        return gmp_strval($cost);
    }

    /**
     * Store the reward
     */
    protected function createReward($forManagementCost, $forDelegators)
    {
        $reward = new Reward([
            'management_cost' => $forManagementCost,
            'delegators' => $forDelegators
        ]);
        $reward->save();

        return $reward;
    }

    /**
     * Let's create and store a payout
     */
    protected function addPayout($address, $amount)
    {
        $payout = new Payout([
            'address' => $address,
            'amount' => gmp_strval($amount),
        ]);
        $payout->save();
    }

    /**
     * Distribute to delegators
     */
    protected function distributeToDelegators($delegators, $amount)
    {
        // Let's calculate how much is delegated
        $delegated = $delegators->reduce(fn ($carry, $delegator) => gmp_add($carry, $delegator['balance']), 0);

        // Now distribute the amount to all
        $remaining = $amount;
        $payouts = collect([]);
        foreach ($delegators as $delegator) {
            // First calculate the share
            $payout = gmp_mul($delegator['balance'], $amount);
            $payout = gmp_div($payout, $delegated);

            $payouts->push([
                'address' => $delegator['address'],
                'amount' => $payout,
            ]);

            $remaining = gmp_sub($remaining, $payout);
        }

        // If some is remained, let's give it to the last one
        if (gmp_cmp($remaining, 0) > 0) {
            $lastPayout = $payouts[$payouts->count() -1];
            $lastPayout['amount'] = gmp_add($lastPayout['amount'], $remaining);
            $payouts[$payouts->count() -1] = $lastPayout;
        }

        // Let's store the payouts
        $payouts->each(fn ($payout) => $this->addPayout($payout['address'], $payout['amount']));
    }

    /**
     * Let's get only elegible delegators
     */
    protected function getElegibleDelegators(Reward $reward)
    {
        return $this->getDelegators($reward)
            ->filter(fn ($delegator) => $this->isDelegatorWhitelisted($delegator))
            ->filter(fn ($delegator) => $this->isDelegatorNotBanned($delegator))
            ->map(fn ($delegator) => [
                'address' => $delegator->address,
                'balance' => $this->getDelegatorBalanceForReward($delegator, $reward)
            ])
            ->filter(fn ($delegator) => $this->isBalanceMoreThanMinimum($delegator['balance']));
    }

    /**
     * Get delegators
     */
    protected function getDelegators($reward)
    {
        return Delegator::eligibleForReward($reward)->get();
    }

    /**
     * Get delegator balance mean
     */
    protected function getDelegatorBalanceForReward($delegator, $reward)
    {
        // Here we need to calculate the mean over all the balance samples
        // for this delegator
        $samples = Sample::ofDelegator($delegator)
            ->forReward($reward)
            ->get();

        // If there are no samples, the the delegator count as 0
        if ($samples->isEmpty()) {
            return 0;
        }

        $sum = $samples->reduce(fn ($carry, $sample) => gmp_add($carry, $sample->balance));
        return gmp_div($sum, $samples->count());
    }

    /**
     * Check that the delegator is not banned
     */
    protected function isDelegatorNotBanned($delegator)
    {
        return !Banned::byAddress($delegator->address)->exists();
    }

    /**
     * Check that the delegator is whitelisted to receive payouts.
     * If there are no whitelisted addresses then all addresses are.
     */
    protected function isDelegatorWhitelisted($delegator)
    {
        $total = Whitelist::count();
        return $total == 0 ?: Whitelist::byAddress($delegator->address)->exists();
    }

    /**
     * Check if delegator balance is more than minimum
     */
    protected function isBalanceMoreThanMinimum($balance)
    {
        return gmp_cmp($balance, config('settings.delegator.min_balance', 0)) >= 0;
    }

    /**
     * Get distributable amount
     */
    protected function getDistibutableAmount()
    {
        $balance = $this->getTribeBalance();
        $pendingPayouts = Payout::pending()->get();

        foreach ($pendingPayouts as $payout) {
            $balance = gmp_sub($balance, $payout->amount);
        }

        return $balance;
    }

    /**
     * Get the tribe reward
     */
    protected function getTribeBalance()
    {
        $balance = $this->representative->balance();
        return $balance['balance'];
    }
}
