<?php


namespace Challange\CarBundle\ImportManager;


use Pimcore\Model\Asset;

interface ImportInterface
{
    public function import($dataObjectName, $key, $parent): array;

    public function setImportLocalizedAttributes(array $importLocalizedAttributes): void;

    public function setImportAttributes(array $importAttributes): void;

    public function setImportLocales(array $importLocales): void;

    public function setAsset(?Asset $asset): void;

}
