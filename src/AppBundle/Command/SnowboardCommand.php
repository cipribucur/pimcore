<?php

namespace AppBundle\Command;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class SnowboardCommand extends AbstractCommand
{
    const SERVER_IP = '192.168.128.1';

    const IMPORT_PATH = 'var/import/snowboard.csv';

    /**
     * @var Client
     */
    private Client $api;

    /**
     * @var array
     */
    private array $snowboardLocation = [];

    /**
     * @var array|string[]
     */
    private array $fieldsType = [
        'title'       => 'input',
        'description' => 'textarea',
        'geoLocation' => 'geopoint',
        'tags'        => 'multiselect',
    ];

    /**
     * @var array|string[]
     */
    private array $validateValueTypes = [
        'input', 'textarea', 'multiselect'
    ];

    public function configure()
    {
        $this->setName('app:snowboard:import')
             ->setDescription('Imports from var/import/snowboard.csv.Please save an env with the `apiKey`=...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * for convenience reasons I put the api key in the env
         */
        if (empty($_ENV['apiKey'])) {
            $output->writeln("ApiKey is empty. Please add as an env var the api Key ");
        }

        $this->setApiClient();

        /**
         * we go through each row of the csv and import it
         */
        foreach ($this->getCsv() as $snowboardLocation) {
            $this->snowboardLocation = $snowboardLocation;
            $keyErrors               = $this->parseRow($this->getObjectApiEndpoint(), $this->getHeaders());
        }

        /**
         * if we find any errors we log in console their keys
         */
        if (!empty($keyErrors)) {
            $output->writeln('done, but with this key errors: ' . $keyErrors);
            return;
        }
        $output->writeln('done');
    }

    /**
     * @return array
     */
    private function getHeaders() : array
    {
        return [
            'X-API-Key' => $_ENV['apiKey'],
        ];
    }

    /**
     * @return string
     */
    private function getBaseUri() : string
    {
        return 'https://' . self::SERVER_IP . '/webservice/rest/';
    }

    /**
     * @return string
     */
    private function getObjectApiEndpoint()
    {
        return $this->getBaseUri() . 'object';
    }

    /**
     * we set the client for multiple requests
     */
    private function setApiClient() : void
    {
        $this->api = new Client(['base_uri' => $this->getBaseUri()]);
        return;
    }

    /**
     * @param $newObjectUri
     * @param $headers
     * @return string
     */
    private function parseRow($newObjectUri, $headers): string
    {
        //key is mandatory for the request
        if (empty($this->snowboardLocation['key'])) {
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

    /**
     * Here we get from a csv file to an array structure
     *
     * @return array
     */
    private function getCsv() : array
    {
        $csv       = array_map('str_getcsv', file(self::IMPORT_PATH));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        return $csv;
    }

    /**
     * we structure the body of the request to the endpoint
     *
     * @return array
     */
    private function getBody(): array
    {
        $requestArray = [
            "className" => "SnowboardLocations",
            "key"       => $this->snowboardLocation['key'],
            "parentId"  => 1,
        ];
        $elements     = [];
        foreach ($this->fieldsType as $field => $type) {
            if ($this->validateElement($field, $type)) {
                $elements[] = $this->getElement($field, $type);
            }
        }
        $requestArray['elements'] = $elements;

        return $requestArray;
    }

    /**
     * @param string $field
     * @param string $type
     * @return bool
     */
    private function validateElement(string $field, string $type) : bool
    {
        if (in_array($type, $this->validateValueTypes)) {
            return !empty($this->snowboardLocation[$field]);
        }

        if ($type == 'geopoint') {
            if (empty($this->snowboardLocation['geoLocation_longitude']) or
                empty($this->snowboardLocation['getLocation_latitude'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $field
     * @param $type
     * @return array
     */
    private function getElement($field, $type): array
    {
        switch ($type) {
            case 'input':
                return $this->getInputElement($field);
            case 'textarea':
                return $this->getTextareaElement($field);
            case 'geopoint':
                return $this->getGeopointElement($field);
            case 'multiselect':
                return $this->getMultiselectElement($field);
        }

        return [];
    }

    /**
     * @param $field
     * @return array
     */
    private function getInputElement($field)
    {
        return [
            'type'     => 'input',
            'value'    => $this->snowboardLocation[$field],
            'name'     => $field,
            'language' => null,
        ];
    }

    /**
     * @param $field
     * @return array
     */
    private function getTextareaElement($field)
    {
        return [
            'type'     => 'textarea',
            'value'    => $this->snowboardLocation[$field],
            'name'     => $field,
            'language' => null,
        ];
    }

    /**
     * @param $field
     * @return array
     */
    private function getGeopointElement($field)
    {
        return [
            'type'     => 'geopoint',
            'value'    => [
                'longitude' => $this->snowboardLocation['geoLocation_longitude'],
                'latitude'  => $this->snowboardLocation['getLocation_latitude'],
            ],
            'name'     => $field,
            'language' => null,
        ];
    }

    /**
     * @param $field
     * @return array
     */
    private function getMultiselectElement($field)
    {
        $tags = explode(',', $this->snowboardLocation[$field]);

        return [
            'type'     => 'multiselect',
            'value'    => $tags,
            'name'     => $field,
            'language' => null,
        ];
    }
}
