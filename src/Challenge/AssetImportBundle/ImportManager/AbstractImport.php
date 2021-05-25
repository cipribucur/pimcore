<?php


namespace Challenge\AssetImportBundle\ImportManager;


use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Cars;
use Pimcore\Model\Element\Service;

abstract class AbstractImport
{
    private array $importAttributes = [];

    private array $importLocalizedAttributes = [];

    private array $importLocales = [];

    public function import($dataObject, $key, $parent): array
    {
        $failedRows = [];
        /**
         * we go through each row of the csv and import it
         */
        foreach ($this->getDataToImport() as $row) {
            // Create a new object
            $newObject = $dataObject::create();

            $newObject->setKey(Service::getValidKey($row[$key], 'object'));
            $newObject->setPublished(true);
            $newObject->setParentId($parent);

            try {
                $this->setObjectAttributes($newObject, $row);
                $this->setObjectLocalizedAttributes($newObject, $row);
                $newObject->save(["versionNote" => 'asset_checksum_' . $this->getAsset()->getChecksum()]);
            } catch (Exception $e) {
                $failedRows[] = $key;
            }
        }

        return $failedRows;
    }

    /**
     * @var Asset|null
     */
    private ?Asset $asset;

    /**
     * @param array|string[] $importLocalizedAttributes
     */
    public function setImportLocalizedAttributes(array $importLocalizedAttributes): void
    {
        $this->importLocalizedAttributes = $importLocalizedAttributes;
    }

    /**
     * @param array $importAttributes
     */
    public function setImportAttributes(array $importAttributes): void
    {
        $this->importAttributes = $importAttributes;
    }

    /**
     * @param array|string[] $importLocales
     */
    public function setImportLocales(array $importLocales): void
    {
        $this->importLocales = $importLocales;
    }

    /**
     * @param Asset|null $asset
     */
    public function setAsset(?Asset $asset): void
    {
        $this->asset = $asset;
    }

    /**
     * @return Asset|null
     */
    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    protected function setObjectAttributes(DataObject $dataObject, array $car): void
    {
        foreach ($this->importAttributes as $attr) {
            $dataObject->set($attr, $car[$attr]);
        }
    }

    protected function setObjectLocalizedAttributes(DataObject $dataObject, array $car): void
    {
        foreach ($this->importLocales as $localization) {
            foreach ($this->importLocalizedAttributes as $localAttr) {
                $dataObject->set($localAttr, $car[$localAttr], $localization);
            }
        }
    }

    protected function getDataToImport(): array
    {
        return [];
    }
}
