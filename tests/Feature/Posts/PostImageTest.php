<?php

declare(strict_types=1);

namespace Tests\Feature\Posts;

use App\Enums\PostType;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_storing_a_post_attaches_its_featured_image(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $image = Image::factory()->create(['post_id' => null]);

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'A Post',
            'type' => PostType::Post->value,
            'body' => 'Body',
            'featured_image_id' => $image->id,
        ]);

        $post = Post::query()->where('title', 'A Post')->firstOrFail();
        $this->assertSame($post->id, $image->refresh()->post_id);
        $this->assertSame($image->id, $post->featured_image_id);
    }

    public function test_storing_a_post_attaches_images_referenced_inline_in_the_body(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $image = Image::factory()->create(['post_id' => null]);

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'A Post',
            'type' => PostType::Post->value,
            'body' => "Some text\n\n![alt text]({$image->path})\n\nmore text",
        ]);

        $post = Post::query()->where('title', 'A Post')->firstOrFail();
        $this->assertSame($post->id, $image->refresh()->post_id);
    }

    public function test_updating_a_post_attaches_newly_referenced_images(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $post = Post::factory()->create(['body' => 'Original body']);
        $image = Image::factory()->create(['post_id' => null]);

        $this->actingAs($user)->put(route('dashboard.posts.update', $post), [
            'title' => $post->title,
            'type' => $post->type->value,
            'body' => "Updated body\n\n![]({$image->path})",
        ]);

        $this->assertSame($post->id, $image->refresh()->post_id);
    }

    public function test_featured_image_id_must_reference_an_existing_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'A Post',
                'type' => PostType::Post->value,
                'body' => 'Body',
                'featured_image_id' => 99999,
            ])
            ->assertInvalid(['featured_image_id']);
    }

    public function test_to_html_rewrites_inline_images_to_cloudflare_transform_urls(): void
    {
        Config::set('services.cloudflare_images.domain', 'img.example.test');
        $image = Image::factory()->create(['path' => 'images/abc.jpg', 'width' => 800, 'height' => 600]);
        $post = Post::factory()->create(['body' => '![alt text](images/abc.jpg)']);

        $html = $post->toHtml();

        $this->assertStringContainsString(
            'src="https://img.example.test/cdn-cgi/image/width=1200,quality=80,format=auto/images/abc.jpg"',
            $html,
        );
        $this->assertStringContainsString('width="800"', $html);
        $this->assertStringContainsString('height="600"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    public function test_to_html_falls_back_to_the_plain_storage_url_without_a_cloudflare_domain(): void
    {
        Storage::fake('r2');
        Config::set('services.cloudflare_images.domain', null);
        Image::factory()->create(['path' => 'images/abc.jpg']);
        $post = Post::factory()->create(['body' => '![alt text](images/abc.jpg)']);

        $html = $post->toHtml();

        $this->assertStringContainsString(Storage::disk('r2')->url('images/abc.jpg'), $html);
    }
}
