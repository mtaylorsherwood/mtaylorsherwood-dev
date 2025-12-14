<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SensorReading;
use Illuminate\Support\Arr;

final readonly class RecordReadingAction
{
    /**
     * @param array<string, mixed> $readings
     */
    public function execute(array $readings): bool
    {
        try {
            SensorReading::createAndSave(Arr::dot($readings));
        } catch (\DateMalformedStringException) {
            return false;
        }

        return true;
    }
}
