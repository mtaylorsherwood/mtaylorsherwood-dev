<?php

declare(strict_types=1);

namespace Tests\Feature\Images;

use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->post(route('dashboard.images.store'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_upload_an_image(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('dashboard.images.store'), [
                'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
            ]);

        $response->assertCreated();
        $response->assertJsonStructure(['id', 'path', 'width', 'height', 'url']);
        $response->assertJson(['width' => 800, 'height' => 600]);

        $path = $response->json('path');
        $this->assertDatabaseHas('images', ['path' => $path, 'width' => 800, 'height' => 600]);
        Storage::disk('r2')->assertExists($path);
    }

    public function test_uploading_the_same_file_twice_does_not_duplicate_it(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $first = $this->actingAs($user)->post(route('dashboard.images.store'), ['image' => $file]);
        $second = $this->actingAs($user)->post(route('dashboard.images.store'), ['image' => $file]);

        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertSame(1, Image::query()->count());
    }

    public function test_non_image_files_are_rejected(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.images.store'), [
                'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->assertInvalid(['image']);
    }

    public function test_oversized_images_are_rejected(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.images.store'), [
                'image' => UploadedFile::fake()->image('big.jpg', 800, 600)->size(9000),
            ])
            ->assertInvalid(['image']);
    }

    public function test_images_larger_than_the_max_dimensions_are_rejected(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.images.store'), [
                'image' => UploadedFile::fake()->image('huge.jpg', 7000, 7000),
            ])
            ->assertInvalid(['image']);
    }
}
