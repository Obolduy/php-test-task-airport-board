<?php

namespace App\Entity;

class Flight
{
    private Airport $fromAirport;
    private string $fromTime;
    private Airport $toAirport;
    private string $toTime;

    public function __construct(Airport $fromAirport, string $fromTime, Airport $toAirport, string $toTime)
    {
        $this->fromAirport = $fromAirport;
        $this->fromTime = $fromTime;
        $this->toAirport = $toAirport;
        $this->toTime = $toTime;
    }

    public function getFromAirport(): Airport
    {
        return $this->fromAirport;
    }

    public function getFromTime(): string
    {
        return $this->fromTime;
    }

    public function getToAirport(): Airport
    {
        return $this->toAirport;
    }

    public function getToTime(): string
    {
        return $this->toTime;
    }

    /**
     * Высчитывает разницу между часовыми поясами.
     * @param string $fromTZ Часовой пояс аэропорта вылета.
     * @param string $toTZ Часовой пояс аэропорта назначения.
     * @return int Разность часовых поясов.
     */
    private function calculateTZDifference(string $fromTZ, string $toTZ): int
    {
        $toTZ = (int)substr($toTZ, -3, 3);
        $fromTZ = (int)substr($fromTZ, -3, 3);

        // Из-за того, что мы находим разность TZ, мы должны считать модули чисел
        $toTZ = ($toTZ > 0) ? $toTZ : -$toTZ;
        $fromTZ = ($fromTZ > 0) ? $fromTZ : -$fromTZ;

        $difference = 60 * ($toTZ - $fromTZ);

        // Если разность TZ не попадает в промежуток от 720 до -720,
        // мы должны уменьшить или увеличить число на 720 минут (12 часов).
        if ($difference > 720) $difference -= 720;
        if ($difference < -720) $difference += 720;

        return $difference;
    }

    private function calculateMinutesFromStartDay(string $time): int
    {
        [$hour, $minutes] = explode(':', $time, 2);

        return 60 * (int) $hour + (int) $minutes;
    }
}