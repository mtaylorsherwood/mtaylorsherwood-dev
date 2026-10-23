<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Book;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BooksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_page_lists_all_books(): void
    {
        Book::factory()->count(2)->create();
        Book::factory()->currentlyReading()->create();

        $this->get(route('books'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/books/Index')
                ->has('books', 3),
            );
    }

    public function test_books_page_excludes_to_be_read_books(): void
    {
        Book::factory()->count(2)->create();
        Book::factory()->toBeRead()->create();

        $this->get(route('books'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/books/Index')
                ->has('books', 2),
            );
    }

    public function test_books_page_lists_recent_published_recaps(): void
    {
        Post::factory()->recap()->published()->count(2)->create();
        Post::factory()->recap()->create(); // draft, should not appear

        $this->get(route('books'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/books/Index')
                ->has('recaps', 2),
            );
    }

    public function test_the_cached_book_list_is_invalidated_when_a_book_is_added(): void
    {
        Book::factory()->count(2)->create();

        $this->get(route('books'))
            ->assertInertia(fn(Assert $page) => $page->has('books', 2));

        Book::factory()->create();

        $this->get(route('books'))
            ->assertInertia(fn(Assert $page) => $page->has('books', 3));
    }
}
