<?php

namespace App\Entity;

use DateTime;

class Flight
{
    private Airport $fromAirport;
    private string $fromTime;
    private Airport $toAirport;
    private string $toTime;
    private int $fullDuration;
    private string $fromTZ;
    private string $toTZ;

    public function __construct(Airport $fromAirport, string $fromTime, Airport $toAirport, string $toTime)
    {
        $this->fromAirport = $fromAirport;
        $this->fromTime = $fromTime;
        $this->toAirport = $toAirport;
        $this->toTime = $toTime;
        $this->fullDuration = $this->calculateDurationMinutes();
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

    public function getFullDuration(): string
    {
        return $this->fullDuration;
    }

    public function getFromTZ(): string
    {
        return $this->fromTZ;
    }

    public function getToTZ(): string
    {
        return $this->toTZ;
    }

    /**
     * Высчитывает полную продолжительность полёта (учитывая часовые пояса) в минутах.
     * @return int Продолжительность полёта в минутах.
     */
    public function calculateDurationMinutes(): int
    {
        $fromTimeMinutes = $this->calculateMinutesFromStartDay($this->fromTime);
        $toTimeMinutes = $this->calculateMinutesFromStartDay($this->toTime);

        $duration = $fromTimeMinutes - $toTimeMinutes;

        // если разница получилась неотрицательная, мы должны отнять ее от количества минут в сутках - 1440
        if ($duration > 0) {
            $duration = 1440 - $duration;
        }

        $TZDifference = $this->calculateTZDifference($this->fromAirport->getTimeZone(), $this->toAirport->getTimeZone());

        $fullDuration = $duration + $TZDifference;

        // перелеты, которые проходят в рамках одного дня и заканчивающиеся до полуночи,
        // имеют отрицательный $duration, вследствие этого приходится умножать число на -1
        if ($fullDuration < 0) $fullDuration *= -1;

        return $fullDuration;
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