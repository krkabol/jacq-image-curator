<?php declare(strict_types=1);

namespace App\Services;

final class AppConfiguration
{

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    public function getPlatform(): ?string
    {
        if(!isset($this->config['environment'])){
            return null;
        }
        return $this->config['environment'];
    }

}
