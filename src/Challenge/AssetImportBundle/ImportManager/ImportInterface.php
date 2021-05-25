<?php


namespace Challenge\AssetImportBundle\ImportManager;


use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;

interface ImportInterface
{
    /**
     * @param DataObject $dataObject
     * @param $key
     * @param $parent
     * @return array
     */
    public function import(DataObject $dataObject, $key, $parent): array;

    /**
     * @param array $importLocalizedAttributes
     */
    public function setImportLocalizedAttributes(array $importLocalizedAttributes): void;

    /**
     * @param array $importAttributes
     */
    public function setImportAttributes(array $importAttributes): void;

    /**
     * @param array $importLocales
     */
    public function setImportLocales(array $importLocales): void;

    /**
     * @param Asset|null $asset
     */
    public function setAsset(?Asset $asset): void;

}
