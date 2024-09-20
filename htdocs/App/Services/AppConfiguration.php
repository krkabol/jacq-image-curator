<?php declare(strict_types = 1);

namespace App\Services;

final readonly class AppConfiguration
{
    /**
     * @param array $config
     */
    public function __construct(private array $config)
    {
    }

    public function getPlatform(): ?string
    {
        if (!isset($this->config['environment'])) {
            return null;
        }

        return $this->config['environment'];
    }

}
