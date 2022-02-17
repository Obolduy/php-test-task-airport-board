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
     * @return int полная продолжительность полета, включая часовые пояса.
     */
    public function calculateDurationMinutes(): int
    {
        $duration = $this->calculateRawDuration();

        $TZDifference = $this->calculateTZDifference($this->fromAirport->getTimeZone(), $this->toAirport->getTimeZone());

        $fullDuration = $duration + $TZDifference;

        // Перелеты, которые проходят в рамках одного дня и заканчивающиеся до полуночи,
        // имеют отрицательный $duration, вследствие этого приходится умножать число на -1
        if ($fullDuration < 0) $fullDuration *= -1;

        // Если хоть один из часовых поясов - отрицательный, тогда мы отнимаем от суток
        // разницу часовых поясов, таким образом "грубая разница" будет равняться этой разности,
        // однако, по факту, "настоящая разница" может достигать при определенных обстоятельствах и 23 часов, но в силу того,
        // что формат времени отправления подразумевает только часы и минуты, эти подробности опускаются
        // и возвращается "грубая разница"
        if ($this->fromTZ < 0 || $this->toTZ < 0) {
            $difference = 1440 - abs($TZDifference);

            if ($TZDifference < 0) $difference *= -1;

            $fullDuration = $duration + $difference;
        }

        return $fullDuration;
    }

    /**
     * Вычисляет время полета без учета часовых зон.
     * @return int Продолжительность полёта без часовых зон в минутах.
     */
    public function calculateRawDuration(): int
    {
        $duration = $this->calculateMinutesFromStartDay($this->fromTime) - $this->calculateMinutesFromStartDay($this->toTime);

        // если разница получилась неотрицательная, мы должны отнять ее от количества минут в сутках - 1440
        if ($duration > 0) {
            $duration = 1440 - $duration;
        }

        return $duration;
    }

    /**
     * Высчитывает разницу между часовыми поясами.
     * @param string $fromTZ Часовой пояс аэропорта вылета.
     * @param string $toTZ Часовой пояс аэропорта назначения.
     * @return int Разность часовых поясов.
     */
    public function calculateTZDifference(string $fromTZ, string $toTZ): int
    {
        $this->toTZ = (int)substr($toTZ, -3, 3);
        $this->fromTZ = (int)substr($fromTZ, -3, 3);

        // Если оба пояса находятся к востоку от Гринвича, мы находим их разность
        if ($this->fromTZ > 0 && $this->toTZ > 0) {
            $TZ = $this->toTZ - $this->fromTZ;
        }

        $TZ = $TZ ?? abs($this->toTZ) + abs($this->fromTZ);
        
        $difference = 60 * $TZ;

        // если аэропорт отправления расположен западнее Гринвича, делим число на -1,
        // потому что до этого мы искали модули чисел, игнорируя, что уменьшаемое число могло быть отрицательным
        if ($this->toTZ < 0) {
            $difference *= -1;
        }

        return $difference;
    }

    private function calculateMinutesFromStartDay(string $time): int
    {
        [$hour, $minutes] = explode(':', $time, 2);

        return 60 * (int) $hour + (int) $minutes;
    }
}