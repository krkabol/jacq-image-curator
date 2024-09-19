<?php

declare(strict_types=1);

namespace App\Model\IIIF;
class IiifManifest_v3
{
    protected  $default;
    public function __construct()
    {
        $filePath = '../App/Model/IIIF/v3.json';
//        $filePath = '../App/Model/kiel.json';
//https://services.jacq.org/jacq-services/rest/objects/specimens/1739342
        $this->default = json_decode(file_get_contents($filePath), true);
    }

    public function getDefault()
    {
        return $this->default;
    }

}
