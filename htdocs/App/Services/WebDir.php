<?php declare(strict_types=1);


namespace App\Services;

readonly class WebDir
{

    public function __construct(protected string $wwwDir)
    {
    }

    public function getPath($fromBaseDir = '')
    {
        return $this->wwwDir . DIRECTORY_SEPARATOR . $fromBaseDir;
    }

}