<?php

namespace Challenge\AssetImportBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class ChallengeAssetImportBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/challengeassetimport/js/pimcore/startup.js'
        ];
    }
}