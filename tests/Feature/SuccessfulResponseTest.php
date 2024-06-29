<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SuccessfulResponseTest extends TestCase
{
    /**
     * A basic test for the app response.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
