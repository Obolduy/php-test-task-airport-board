<?php

namespace App\Repository;

use App\Entity\Airport;
use App\Service\JsonParser;

class AirportRepository extends AbstractRepository
{
    private const CODE_DATA_KEY = 'code';
    private const NAME_DATA_KEY = 'name';
    private const CITY_DATA_KEY = 'city';
    private const TIMEZONE_DATA_KEY = 'timeZone';

    private JsonParser $jsonParser;

    public function __construct(JsonParser $jsonParser)
    {
        $this->jsonParser = $jsonParser;
    }

    public function getAirport(string $code): Airport
    {
        $airports = $this->getData();
        $this->assertAirportExists($airports, $code);

        return $airports[$code];
    }

    protected function loadData(): array
    {
        $airports = [];
        foreach ($this->jsonParser->load('airport.json') as $airportData) {
            $this->assertKeysExists($airportData, [self::CODE_DATA_KEY]);

            $airports[$airportData[self::CODE_DATA_KEY]] = $this->buildAirport($airportData);
        }

        return $airports;
    }

    private function buildAirport(array $airportData): Airport
    {
        $this->assertKeysExists($airportData, [self::NAME_DATA_KEY, self::CITY_DATA_KEY]);

        return new Airport(
            $airportData[self::CODE_DATA_KEY],
            $airportData[self::NAME_DATA_KEY],
            $airportData[self::CITY_DATA_KEY],
            $airportData[self::TIMEZONE_DATA_KEY]
        );
    }

    private function assertAirportExists(array $airports, string $code): void
    {
        if(!isset($airports[$code])) {
            throw new \Exception(sprintf(
                'Airport with code "%s" not exists. Available airport codes: "%s".',
                $code,
                implode('", "', array_keys($airports))
            ));
        }
    }
}