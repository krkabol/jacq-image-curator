<?php declare(strict_types=1);


namespace app\Services;

class WebDir
{

    protected $wwwDir;

    public function __construct($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }

    public function getPath($fromBaseDir = '')
    {
        return $this->wwwDir . DIRECTORY_SEPARATOR . $fromBaseDir;
    }

}
