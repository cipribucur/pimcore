<?php


namespace Challenge\AssetImportBundle\ImportManager;


use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;

interface ImportInterface
{
    public function import(DataObject $dataObject, $key, $parent): array;

    public function setImportLocalizedAttributes(array $importLocalizedAttributes): void;

    public function setImportAttributes(array $importAttributes): void;

    public function setImportLocales(array $importLocales): void;

    public function setAsset(?Asset $asset): void;

}
