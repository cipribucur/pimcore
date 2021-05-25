<?php


namespace Challange\CarBundle\ImportManager;

class CsvImport extends AbstractImport implements ImportInterface
{
    /**
     * Here we get from a csv file to an array structure
     *
     * @return array
     */
    protected function getDataToImport(): array
    {
        $csv = array_map('str_getcsv', file($this->getAsset()->getFileSystemPath()));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        return $csv;
    }
}
