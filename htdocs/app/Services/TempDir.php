<?php declare(strict_types=1);

namespace App\Services;

class TempDir
{

    protected $dir;

    public function __construct($dir)
    {
        $this->dir = $dir . DIRECTORY_SEPARATOR . 'curator';
    }

    public function getPath($fromBaseDir = '')
    {
        return $this->dir . DIRECTORY_SEPARATOR . $fromBaseDir;
    }

}
