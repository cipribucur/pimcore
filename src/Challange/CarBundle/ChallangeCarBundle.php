<?php

namespace Challange\CarBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class ChallangeCarBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/challangecar/js/pimcore/startup.js'
        ];
    }
}