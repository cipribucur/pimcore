<?php


namespace Challange\CarBundle\Controller\Admin;


use Exception;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Cars;
use Pimcore\Model\Element\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\DataObject;

class CarImportController extends AdminController
{
    const MIMETYPE = 'text/csv';
    /**
     * @var Asset|null
     */
    private ?Asset $asset;

    private array $importAttributes = [
        'articleNumber',
        'manufacturer',
        'model',
        'cylinders',
        'horsepower',
        'productionYear'
    ];

    private array $importLocalizedAttributes = [
      'description'
    ];

    private array $importLocales = ['en', 'de'];

    private array $failedArticles = [];

    /**
     * @Route("/carimport", name="pimcore_admin_asset_carimport", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function carImportAction(Request $request): JsonResponse
    {
        $this->asset = Asset::getById($request->get('id'));

        if (!$this->asset) {
            throw $this->createNotFoundException('Asset not found');
        }

        if (!$this->asset->isAllowed('view')) {
            //import the csv into objects
            throw $this->createNotFoundException('Asset not allowed to access');
        }

        if (!$this->asset->getMimetype() == static::MIMETYPE) {
            //import the csv into objects
            throw $this->createNotFoundException('Asset is not a CSV file.');
        }

        $this->import();

        return $this->adminJson([
            'success' => true,
            'failedArticle' => $this->getFailedArticles()
        ]);
    }

    /**
     * @return array
     */
    public function getFailedArticles(): array
    {
        return $this->failedArticles;
    }

    private function import(): void
    {
        /**
         * we go through each row of the csv and import it
         */
        foreach ($this->getCsv() as $car) {
            // Create a new object
            $newCar = new DataObject\Cars();

            $newCar->setKey(Service::getValidKey($car['articleNumber'], 'object'));
            $newCar->setPublished(true);
            $newCar->setParentId(1);

            try {
                $this->setAttributes($newCar, $car);
                $this->setLocalizedAttributes($newCar, $car);
                $newCar->save(["versionNote" => 'asset_checksum_' . $this->asset->getChecksum()]);
            } catch (Exception $e) {
                $this->failedArticles[] = $car['articleNumber'];
            }

        }
    }

    private function setAttributes(Cars $newCar, array $car): void
    {
        foreach ($this->importAttributes as $attr) {
            $newCar->set($attr, $car[$attr]);
        }
    }

    private function setLocalizedAttributes(Cars $newCar, array $car): void
    {
        foreach ($this->importLocales as $localization) {
            foreach ($this->importLocalizedAttributes as $localAttr) {
                $newCar->set($localAttr, $car[$localAttr], $localization);
            }
        }
    }

    /**
     * Here we get from a csv file to an array structure
     *
     * @return array
     */
    private function getCsv(): array
    {
        $csv = array_map('str_getcsv', file($this->asset->getFileSystemPath()));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        return $csv;
    }

}
