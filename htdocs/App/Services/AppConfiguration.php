<?php declare(strict_types = 1);

namespace App\Services;

final readonly class AppConfiguration
{

    public const string VERSION_VARIABLE = 'GIT_TAG';

    /**
     * @param mixed[] $config
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

    public function isProduction(): bool
    {
        return $this->getPlatform() === 'production';
    }

    public function getVersion(): string
    {
        if (getenv(self::VERSION_VARIABLE) !== false) {
            return getenv(self::VERSION_VARIABLE);
        }

        return 'unknown version';
    }

}
