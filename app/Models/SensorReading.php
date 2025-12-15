<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'sensor',
        'uid',
        'timestamp',
        'temperature',
        'humidity',
        'pressure',
        'noise',
        'pm1',
        'pm2_5',
        'pm10',
        'voltage',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'date:Y-m-d H:i:s',
            'temperature' => 'decimal:2',
            'humidity' => 'decimal:2',
            'pressure' => 'decimal:2',
            'noise' => 'decimal:2',
            'pm1' => 'decimal:2',
            'pm2_5' => 'decimal:2',
            'pm3' => 'decimal:2',
            'pm10' => 'decimal:2',
            'voltage' => 'decimal:3',
        ];
    }


    /**
     * @param array<string, string> $attributes
     *
     * @throws \DateMalformedStringException
     */
    public static function createAndSave(array $attributes): SensorReading
    {
        $reading = new SensorReading()->fill(
            [
                'sensor' => $attributes['nickname'],
                'uid' => $attributes['uid'],
                'timestamp' => new DateTime($attributes['timestamp'])->format('Y-m-d H:i:s'),
                'temperature' => $attributes['readings.temperature'],
                'humidity' => $attributes['readings.humidity'],
                'pressure' => $attributes['readings.pressure'],
                'noise' => $attributes['readings.noise'],
                'pm1' => $attributes['readings.pm1'],
                'pm2_5' => $attributes['readings.pm2_5'],
                'pm10' => $attributes['readings.pm10'],
                'voltage' => $attributes['readings.voltage'],
            ],
        );

        $reading->save();

        return $reading;
    }
}
