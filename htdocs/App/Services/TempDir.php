<?php declare(strict_types = 1);

namespace App\Services;

class TempDir
{

    protected string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir . DIRECTORY_SEPARATOR . 'curator';
    }

    public function getPath(string $fromBaseDir = ''): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . $fromBaseDir;
    }

}
