<?php

declare(strict_types=1);

namespace Tests\Feature\Books;

use App\Enums\BookCategory;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard.books.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_all_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('dashboard.books.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('books/Index')
                ->has('books', 3),
            );
    }

    public function test_the_cached_dashboard_book_list_is_invalidated_when_a_book_is_added(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('dashboard.books.index'))
            ->assertInertia(fn(Assert $page) => $page->has('books', 3));

        Book::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard.books.index'))
            ->assertInertia(fn(Assert $page) => $page->has('books', 4));
    }

    public function test_the_cached_genre_and_series_suggestions_are_invalidated_on_change(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['genre' => 'Fantasy', 'series' => 'The Wheel of Time']);

        $this->actingAs($user)
            ->get(route('dashboard.books.create'))
            ->assertInertia(
                fn(Assert $page) => $page
                ->where('genres', ['Fantasy'])
                ->where('series', ['The Wheel of Time']),
            );

        Book::factory()->create(['genre' => 'Science Fiction', 'series' => 'The Culture']);

        $this->actingAs($user)
            ->get(route('dashboard.books.create'))
            ->assertInertia(
                fn(Assert $page) => $page
                ->where('genres', ['Fantasy', 'Science Fiction'])
                ->where('series', ['The Culture', 'The Wheel of Time']),
            );
    }

    public function test_users_can_add_a_book(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [
                'title' => 'Test Driven Laravel',
                'author' => 'Adam Wathan',
                'category' => BookCategory::NonFiction->value,
                'genre' => 'Technology',
                'pages' => 300,
                'series' => null,
                'review' => null,
                'started_at' => '2026-01-01',
                'finished_at' => null,
            ])
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Test Driven Laravel',
            'author' => 'Adam Wathan',
            'finished_at' => null,
        ]);
    }

    public function test_book_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [])
            ->assertInvalid(['title', 'author', 'category', 'genre', 'pages']);
    }

    public function test_users_can_add_a_book_to_be_read(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [
                'title' => 'Test Driven Laravel',
                'author' => 'Adam Wathan',
                'category' => BookCategory::NonFiction->value,
                'genre' => 'Technology',
                'pages' => 300,
                'series' => null,
                'review' => null,
                'started_at' => null,
                'finished_at' => null,
            ])
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Test Driven Laravel',
            'started_at' => null,
            'finished_at' => null,
        ]);
    }

    public function test_finished_at_requires_a_started_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [
                'title' => 'Test Driven Laravel',
                'author' => 'Adam Wathan',
                'category' => BookCategory::NonFiction->value,
                'genre' => 'Technology',
                'pages' => 300,
                'started_at' => null,
                'finished_at' => '2026-01-01',
            ])
            ->assertInvalid(['started_at']);
    }

    public function test_finished_at_must_not_be_before_started_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [
                'title' => 'Test Driven Laravel',
                'author' => 'Adam Wathan',
                'category' => BookCategory::NonFiction->value,
                'genre' => 'Technology',
                'pages' => 300,
                'started_at' => '2026-01-15',
                'finished_at' => '2026-01-01',
            ])
            ->assertInvalid(['finished_at']);
    }

    public function test_users_can_mark_a_book_as_a_reread(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.books.store'), [
                'title' => 'Test Driven Laravel',
                'author' => 'Adam Wathan',
                'category' => BookCategory::NonFiction->value,
                'genre' => 'Technology',
                'pages' => 300,
                'is_reread' => true,
                'started_at' => '2026-01-01',
                'finished_at' => '2026-01-10',
            ])
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Test Driven Laravel',
            'is_reread' => true,
        ]);
    }

    public function test_omitting_is_reread_marks_a_book_as_not_a_reread(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['is_reread' => true]);

        $this->actingAs($user)
            ->put(route('dashboard.books.update', $book), $this->payloadFor($book))
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertFalse($book->refresh()->is_reread);
    }

    public function test_users_can_update_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Old Title']);

        $this->actingAs($user)
            ->put(route('dashboard.books.update', $book), $this->payloadFor($book, ['title' => 'New Title']))
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'New Title']);
    }

    public function test_users_can_delete_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->delete(route('dashboard.books.destroy', $book))
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_users_can_start_reading_a_to_be_read_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->toBeRead()->create();

        $this->actingAs($user)
            ->patch(route('dashboard.books.start', $book), ['started_at' => '2026-01-01'])
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertSame('2026-01-01', $book->refresh()->started_at->toDateString());
    }

    public function test_starting_a_book_requires_a_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->toBeRead()->create();

        $this->actingAs($user)
            ->patch(route('dashboard.books.start', $book), [])
            ->assertInvalid(['started_at']);
    }

    public function test_users_can_finish_a_currently_reading_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->currentlyReading()->create(['started_at' => '2026-01-01']);

        $this->actingAs($user)
            ->patch(route('dashboard.books.finish', $book), ['finished_at' => '2026-01-15'])
            ->assertRedirect(route('dashboard.books.index'));

        $this->assertSame('2026-01-15', $book->refresh()->finished_at->toDateString());
    }

    public function test_a_book_cannot_be_finished_before_it_was_started(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->currentlyReading()->create(['started_at' => '2026-01-15']);

        $this->actingAs($user)
            ->patch(route('dashboard.books.finish', $book), ['finished_at' => '2026-01-01'])
            ->assertInvalid(['finished_at']);
    }

    public function test_a_to_be_read_book_cannot_be_finished_directly(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->toBeRead()->create();

        $this->actingAs($user)
            ->patch(route('dashboard.books.finish', $book), ['finished_at' => '2026-01-01'])
            ->assertInvalid(['finished_at']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(Book $book, array $overrides = []): array
    {
        return array_merge([
            'title' => $book->title,
            'author' => $book->author,
            'category' => $book->category->value,
            'genre' => $book->genre,
            'pages' => $book->pages,
            'series' => $book->series,
            'review' => $book->review,
            'started_at' => $book->started_at?->toDateString(),
            'finished_at' => $book->finished_at?->toDateString(),
        ], $overrides);
    }
}
