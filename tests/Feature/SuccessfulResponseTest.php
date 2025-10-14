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

    public function test_the_bookshelf_page_returns_a_successful_response(): void
    {
        $response = $this->get('/bookshelf');

        $response->assertStatus(200);
    }

    public function test_a_table_of_books_can_be_seen_on_the_bookshelf_page(): void
    {
        $response = $this->get('/bookshelf');

        $response->assertSeeText(['The Eye of the World', 'Mistborn', 'A Memory Of Light']);
    }
}
