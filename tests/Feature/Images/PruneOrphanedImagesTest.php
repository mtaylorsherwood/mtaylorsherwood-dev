<?php

declare(strict_types=1);

namespace Tests\Feature\Images;

use App\Models\Image;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneOrphanedImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_unattached_images_older_than_a_day(): void
    {
        Storage::fake('r2');
        $image = Image::factory()->create(['post_id' => null]);
        Storage::disk('r2')->put($image->path, 'contents');
        $image->forceFill(['created_at' => Carbon::now()->subDays(2)])->save();

        $this->artisan('images:prune')->assertSuccessful();

        $this->assertDatabaseMissing('images', ['id' => $image->id]);
        Storage::disk('r2')->assertMissing($image->path);
    }

    public function test_it_leaves_recently_uploaded_unattached_images_alone(): void
    {
        Storage::fake('r2');
        $image = Image::factory()->create(['post_id' => null]);

        $this->artisan('images:prune')->assertSuccessful();

        $this->assertDatabaseHas('images', ['id' => $image->id]);
    }

    public function test_it_leaves_images_attached_to_a_post_alone(): void
    {
        Storage::fake('r2');
        $post = Post::factory()->create();
        $image = Image::factory()->create(['post_id' => $post->id]);
        $image->forceFill(['created_at' => Carbon::now()->subDays(2)])->save();

        $this->artisan('images:prune')->assertSuccessful();

        $this->assertDatabaseHas('images', ['id' => $image->id]);
    }

    public function test_it_leaves_images_attached_to_a_project_alone(): void
    {
        Storage::fake('r2');
        $project = Project::factory()->create();
        $image = Image::factory()->create(['project_id' => $project->id, 'position' => 0]);
        $image->forceFill(['created_at' => Carbon::now()->subDays(2)])->save();

        $this->artisan('images:prune')->assertSuccessful();

        $this->assertDatabaseHas('images', ['id' => $image->id]);
    }
}
