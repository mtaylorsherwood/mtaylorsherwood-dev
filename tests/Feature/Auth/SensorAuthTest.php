<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\SensorReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorAuthTest extends TestCase
{
    use RefreshDatabase;

    private static array $reading_data = [
        "nickname" => "woodside-kitchen-garden",
        "timestamp" => "2025-12-25 10:01:24",
        "readings" => [
            "temperature" => 7.57,
            "humidity" => 49.33,
            "pressure" => 996.22,
            "noise" => 0.41,
            "pm1" => 1.0,
            "pm2_5" => 1.0,
            "pm10" => 2.0,
            "voltage" => 4.954,
        ],
    ];

    public function test_sensor_auth_returns_a_401(): void
    {
        $response = $this->post('api/enviro/saveReading', self::$reading_data);

        $response->assertUnauthorized();
    }

    public function test_sensor_auth_returns_a_200(): void
    {
        config()->set('auth.enviro_username', 'test_user');
        config()->set('auth.enviro_password', 'test_password');

        $response = $this->post('api/enviro/saveReading', self::$reading_data, ['php-auth-user' => 'test_user', 'php-auth-pw' => 'test_password']);

        $response->assertSuccessful();

        $reading = SensorReading::query()->where('timestamp', '2025-12-25 10:01:24')->first();
        $this->assertNotNull($reading);
    }
}
