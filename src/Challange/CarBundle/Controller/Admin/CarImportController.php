<?php


namespace Challange\CarBundle\Controller\Admin;


use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CarImportController extends AdminController
{
    /**
     * @Route("/carimport", name="pimcore_admin_asset_carimport", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function carImportAction(Request $request)
    {
        $jobId = uniqid();
        $filesPerJob = 5;
        $jobs = [];
        $asset = Asset::getById($request->get('id'));

        if (!$asset) {
            throw $this->createNotFoundException('Asset not found');
        }

        if ($asset->isAllowed('view')) {
            $parentPath = $asset->getRealFullPath();
            //import the csv into objects

            $this->import($asset);
        }

        return $this->adminJson([
            'success' => true,
            'jobs' => $jobs,
            'jobId' => $jobId,
        ]);
    }

    public function import(Asset $asset): bool
    {
        /**
         * we go through each row of the csv and import it
         */
        foreach ($this->getCsv() as $car) {
            $this->car = $car;
            $keyErrors = $this->parseRow($this->getObjectApiEndpoint(), $this->getHeaders());
        }

        /**
         * if we find any errors we log in console their keys
         */
        if (!empty($keyErrors)) {

            return;
        }

        return true;
    }

    /**
     * Here we get from a csv file to an array structure
     *
     * @return array
     */
    private function getCsv(): array
    {
        $csv = array_map('str_getcsv', file(self::IMPORT_PATH));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        return $csv;
    }

    /**
     * @param $newObjectUri
     * @param $headers
     * @return string
     */
    private function parseRow($newObjectUri, $headers): string
    {
        //key is mandatory for the request
        if (empty($this->car['key'])) {
            return '';
        }
        $jsonBody = json_encode($this->getBody());
        try {
            $request  = new Request('POST', $newObjectUri, $headers, $jsonBody);
            $response = $this->api->send($request, ['timeout' => 2, 'verify' => false]);
            if ($response->getStatusCode() == 200) {
                return '';
            }
            return $this->snowboardLocation['key'] . ', ';
        } catch (GuzzleException $e) {
            return $this->snowboardLocation['key'] . ', ';
        }
    }
}
