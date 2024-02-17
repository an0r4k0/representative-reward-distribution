<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;

class ConfigGenerator
{
    protected const CONFIG_STUB_PATH = 'stubs/config.toml.stub';

    protected const TRIBE_ADDRESS_ATTRIBUTE = 'TRIBE_ADDRESS';
    protected const TRIBE_WALLET_ID_ATTRIBUTE = 'TRIBE_WALLET_ID';
    protected const MANAGEMENT_ADDRESS_ATTRIBUTE = 'MANAGEMENT_ADDRESS';

    /**
     * Substitutions
     */
    protected $substitutions = [];

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
    }

    public function store($path)
    {
        $this->filesystem->put($path, $this->getConfigContent());
    }

    /**
     * Set tribe address
     */
    public function setTribeAddress($address)
    {
        $this->substitutions[self::TRIBE_ADDRESS_ATTRIBUTE] = $address;
        return $this;
    }

    /**
     * Set tribe wallet id
     */
    public function setTribeWalletId($walletId)
    {
        $this->substitutions[self::TRIBE_WALLET_ID_ATTRIBUTE] = $walletId;
        return $this;
    }

    /**
     * Set management cost address
     */
    public function setManagementCostAddress($address)
    {
        $this->substitutions[self::MANAGEMENT_ADDRESS_ATTRIBUTE] = $address;
        return $this;
    }

    /**
     * Get config stub content
     */
    protected function getConfigContent()
    {
        $attributes = collect($this->substitutions)
            ->keys()
            ->map(fn ($key) => '{{' . $key . '}}')
            ->toArray();

        $values = collect($this->substitutions)
            ->values()
            ->toArray();

        return str_replace(
            $attributes,
            $values,
            $this->filesystem->get(base_path(self::CONFIG_STUB_PATH))
        );
    }
}
