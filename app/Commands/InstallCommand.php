<?php

namespace App\Commands;

use App\Services\ConfigGenerator;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install and configure the application';

    /**
     * Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = config('path.directory');
        $filename = config('path.config_filename');
        $configFilename = $directory . '/' . $filename;

        // Let's make sure the directory exists
        $this->createDirectory($directory);

        // Let's migrate the database
        $this->call('migrate', ['--force']);

        // Let's create the config file,
        // asking the right information
        $tribeAddress = $this->ask('What\'s the tribe PAW address?');
        $tribeWalletId = $this->ask('What\'s the tribe Wallet ID?');
        $managementCostAddress = $this->ask('What\'s the tribe management PAW address?');

        app(ConfigGenerator::class)
            ->setTribeAddress($tribeAddress)
            ->setTribeWalletId($tribeWalletId)
            ->setManagementCostAddress($managementCostAddress)
            ->store($configFilename);
    }

    /**
     * Create the directory
     */
    protected function createDirectory($directory)
    {
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }
    }
}
