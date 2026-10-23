<?php

declare(strict_types=1);

use App\Enums\BookCategory;
use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('ongoing');
            $table->text('blurb');
            $table->longText('body');
            $table->string('website_url')->nullable();
            $table->string('github_url')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('category');
            $table->string('genre');
            $table->unsignedInteger('pages');
            $table->string('series')->nullable();
            $table->boolean('is_reread')->default(false);
            $table->text('review')->nullable();
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();
            $table->timestamps();

            $table->index('finished_at');
            $table->index('author');
            $table->index('genre');
            $table->index('series');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('post');
            $table->string('category')->nullable();
            $table->longText('body');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('category');
            $table->index('status');
            $table->index('published_at');
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('mime_type');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('position')->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index('project_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('featured_image_id')->nullable()->after('category')->constrained('images')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->after('featured_image_id')->constrained('projects')->nullOnDelete();
        });

        $this->populateBookshelf();
        $this->populatePosts();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('featured_image_id');
        });

        Schema::dropIfExists('images');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('books');
    }

    /**
     * Populate the bookshelf with the personal reading history and to-be-read backlog. Skipped
     * in testing, where the books table is populated via factories instead.
     */
    private function populateBookshelf(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $now = now();

        $rows = array_map(
            fn(array $book) => [
                'title' => $book[0],
                'author' => $book[1],
                'category' => $book[2]->value,
                'genre' => $book[3],
                'series' => $book[4],
                'pages' => $book[5],
                'is_reread' => $book[8] ?? false,
                'review' => null,
                'started_at' => $book[6] ?? null,
                'finished_at' => $book[7] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [...$this->finishedBooks(), ...$this->toBeReadBooks()],
        );

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('books')->insert($chunk);
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: BookCategory, 3: string, 4: ?string, 5: int, 6: string, 7: ?string, 8: bool}>
     */
    private function finishedBooks(): array
    {
        return [
            ['The Eye of the World', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 753, '2021-06-01', '2021-06-30', true],
            ['The Great Hunt', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 721, '2021-07-01', '2021-07-31', true],
            ['The Dragon Reborn', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 624, '2021-08-01', '2021-08-31', true],
            ['The Shadow Rising', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 1051, '2021-09-01', '2021-09-30', true],
            ['The Fires of Heaven', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 926, '2021-10-01', '2021-10-31', true],
            ['Lord of Chaos', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 1049, '2023-04-01', '2023-04-30', true],
            ['Call for the Dead', 'John le Carré', BookCategory::Fiction, 'Spy Fiction', 'George Smiley', 150, '2023-11-01', '2024-04-23', false],
            ['The Frugal Wizard\'s Handbook for Surviving Medieval England', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 364, '2024-04-25', '2024-06-10', false],
            ['So Good They Can\'t Ignore You', 'Cal Newport', BookCategory::NonFiction, 'Career & Productivity', null, 230, '2024-06-11', '2024-06-14', false],
            ['Mistborn', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 647, '2024-06-16', '2024-06-21', true],
            ['Deep Work', 'Cal Newport', BookCategory::NonFiction, 'Productivity', null, 263, '2024-06-23', '2024-06-28', false],
            ['The Well of Ascension', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 786, '2024-06-23', '2024-08-23', true],
            ['The Hero of Ages', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 748, '2024-08-23', '2024-08-30', true],
            ['A Crown Of Swords', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 752, '2024-08-31', '2024-09-25', true],
            ['The Path Of Daggers', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 669, '2024-09-27', '2024-12-21', true],
            ['Meditations for Mortals', 'Oliver Burkeman', BookCategory::NonFiction, 'Philosophy & Self-Help', null, 169, '2024-11-03', '2024-12-26', false],
            ['The Staff Engineer\'s Path', 'Tanya Reilly', BookCategory::NonFiction, 'Software Engineering', null, 311, '2024-11-03', '2024-12-30', false],
            ['The Obstacle is the Way', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 192, '2024-12-11', '2024-12-25', false],
            ['Winter\'s Heart', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 705, '2024-12-22', '2025-01-05', true],
            ['Slow Productivity', 'Cal Newport', BookCategory::NonFiction, 'Productivity', null, 222, '2025-01-01', '2025-01-06', false],
            ['Crossroads Of Twilight', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 688, '2025-01-05', '2025-01-21', true],
            ['Clean Code', 'Robert C. Martin', BookCategory::NonFiction, 'Software Engineering', null, 314, '2025-01-06', '2025-01-29', false],
            ['Start With Why', 'Simon Sinek', BookCategory::NonFiction, 'Leadership & Business', null, 225, '2025-01-06', '2025-01-29', false],
            ['Knife Of Dreams', 'Robert Jordan', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 862, '2025-01-21', '2025-02-15', true],
            ['Give and Take', 'Adam Grant', BookCategory::NonFiction, 'Psychology & Business', null, 315, '2025-01-29', '2025-02-23', false],
            ['The DevOps Handbook', 'Gene Kim, Jez Humble, Patrick Debois, John Willis, Nicole Forsgren', BookCategory::NonFiction, 'Software Engineering', null, 421, '2025-01-30', '2025-02-25', false],
            ['The Gathering Storm', 'Robert Jordan, Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 785, '2025-02-15', '2025-02-26', true],
            ['Four Thousand Weeks', 'Oliver Burkeman', BookCategory::NonFiction, 'Philosophy & Productivity', null, 245, '2025-02-23', '2025-02-28', false],
            ['Towers Of Midnight', 'Robert Jordan, Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 958, '2025-03-01', '2025-03-12', true],
            ['Staff Engineer: Leadership beyond the management track', 'Will Larson', BookCategory::NonFiction, 'Software Engineering', null, 318, '2025-03-02', '2025-03-31', false],
            [
                'Conflicted: Why Arguments Are Tearing Us Apart and How They Can Bring Us Together',
                'Ian Leslie',
                BookCategory::NonFiction,
                'Psychology & Communication',
                null,
                257,
                '2025-03-02',
                '2025-03-18',
                false,
            ],
            [
                'The Mom Test: How to talk to customers & learn if your business is a good idea when everyone is lying to you',
                'Rob Fitzpatrick',
                BookCategory::NonFiction,
                'Business & Entrepreneurship',
                null,
                129,
                '2025-03-09',
                '2025-03-31',
                false,
            ],
            ['A Memory Of Light', 'Robert Jordan, Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Wheel of Time', 1010, '2025-03-15', '2025-03-23', true],
            ['Warbreaker', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 652, '2025-03-25', '2025-03-31', false],
            ['Tress of the Emerald Sea', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Hoid\'s Travails', 365, '2025-03-31', '2025-04-03', false],
            [
                'The Pragmatic Programmer: Your Journey To Mastery, 20th Anniversary Edition (2nd Edition)',
                'David Thomas, Andrew Hunt',
                BookCategory::NonFiction,
                'Software Engineering',
                null,
                287,
                '2025-04-01',
                '2025-04-11',
                false,
            ],
            ['Good Inside', 'Becky Kennedy', BookCategory::NonFiction, 'Parenting', null, 298, '2025-04-01', '2025-04-23', false],
            ['The Way of Kings', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 1008, '2025-04-05', '2025-05-21', false],
            ['Tiny Experiments', 'Anne-Laure Le Cunff', BookCategory::NonFiction, 'Productivity & Self-Help', null, 246, '2025-04-12', '2025-04-17', false],
            ['The Hard Thing About Hard Things: Building a Business When There Are No Easy Answers', 'Ben Horowitz', BookCategory::NonFiction, 'Business', null, 275, '2025-05-12', '2025-05-19', false],
            ['The Clean Coder: A Code of Conduct for Professional Programmers', 'Robert C. Martin', BookCategory::NonFiction, 'Software Engineering', null, 204, '2025-05-12', '2025-05-19', false],
            ['Building a Second Brain', 'Tiago Forte', BookCategory::NonFiction, 'Productivity', null, 262, '2025-05-21', '2025-05-25', false],
            [
                'Don\'t Believe Everything You Think',
                'Joseph Nguyen',
                BookCategory::NonFiction,
                'Philosophy & Self-Help',
                null,
                171,
                '2025-05-25',
                '2025-05-27',
                false,
            ],
            ['THE PARA METHOD', 'Tiago Forte', BookCategory::NonFiction, 'Productivity', null, 206, '2025-05-25', '2025-05-25', false],
            ['The Psychology of Money: Timeless lessons on wealth, greed, and happiness', 'Morgan Housel', BookCategory::NonFiction, 'Finance', null, 238, '2025-05-25', '2025-05-27', false],
            ['The Simple Path to Wealth: Your road map to financial independence and a rich, free life', 'J.L. Collins', BookCategory::NonFiction, 'Finance', null, 305, '2025-05-27', '2025-05-31', false],
            ['Words Of Radiance', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 1216, '2025-06-01', '2025-06-26', false],
            ['Refactoring', 'Martin Fowler', BookCategory::NonFiction, 'Software Engineering', null, 404, '2025-06-01', '2025-07-13', false],
            ['Dare to Lead', 'Brené Brown', BookCategory::NonFiction, 'Leadership', null, 272, '2025-06-01', '2025-06-13', false],
            [
                'Kill It with Fire: Manage Aging Computer Systems (and Future Proof Modern Ones)',
                'Marianne Bellotti',
                BookCategory::NonFiction,
                'Software Engineering',
                null,
                233,
                '2025-06-10',
                '2025-06-18',
                false,
            ],
            ['Tidy First?: A Personal Exercise in Empirical Software Design', 'Kent Beck', BookCategory::NonFiction, 'Software Engineering', null, 92, '2025-06-16', '2025-07-04', false],
            ['Edgedancer', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 132, '2025-06-28', '2025-06-29', false],
            ['Alloy Of Law', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 325, '2025-06-29', '2025-07-02', false],
            ['Shadows of Self', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 376, '2025-07-02', '2025-07-07', false],
            ['The Almanack of Naval Ravikant: A Guide to Wealth and Happiness', 'Eric Jorgenson', BookCategory::NonFiction, 'Philosophy & Finance', null, 242, '2025-07-03', '2025-07-07', false],
            ['Bands Of Mourning', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 437, '2025-07-07', '2025-07-12', false],
            ['Essentialism: The Disciplined Pursuit of Less', 'Greg McKeown', BookCategory::NonFiction, 'Productivity', null, 246, '2025-07-07', '2025-07-12', false],
            ['Mistborn Secret History', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 169, '2025-07-12', '2025-07-12', false],
            ['Neuromancer', 'William Gibson', BookCategory::Fiction, 'Science Fiction', 'Sprawl Trilogy', 297, '2025-07-14', '2025-07-28', false],
            [
                'Observability Engineering: Achieving Production Excellence',
                'Charity Majors, Liz Fong-Jones, George Miranda',
                BookCategory::NonFiction,
                'Software Engineering',
                null,
                285,
                '2025-07-14',
                '2025-08-23',
                false,
            ],
            ['Mindset', 'Carol Dweck', BookCategory::NonFiction, 'Psychology', null, 264, '2025-07-14', '2025-07-27', false],
            ['The Emperor\'s Soul', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 103, '2025-07-29', '2025-07-31', false],
            ['Oathbringer', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 1379, '2025-08-01', '2025-08-19', false],
            ['Today Was Fun: A Book About Work (Seriously)', 'Bree Groff', BookCategory::NonFiction, 'Business & Work Culture', null, 260, '2025-08-04', '2025-08-09', false],
            ['Range: Why Generalists Triumph in a Specialized World', 'David Epstein', BookCategory::NonFiction, 'Psychology & Self-Help', null, 292, '2025-08-10', '2025-08-24', false],
            ['Dawnshard', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 247, '2025-08-20', '2025-08-23', false],
            ['Rhythm of War', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 1372, '2025-08-24', '2025-09-08', false],
            ['Originals: How Non-Conformists Move the World', 'Adam Grant', BookCategory::NonFiction, 'Psychology & Business', null, 244, '2025-08-24', '2025-09-04', false],
            ['The Lost Metal', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Mistborn', 500, '2025-09-10', '2025-09-17', false],
            ['Think Again', 'Adam Grant', BookCategory::NonFiction, 'Psychology', null, 297, '2025-09-11', '2025-09-27', false],
            ['The Sunlit Man', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 381, '2025-09-17', '2025-09-20', false],
            ['Ego is the Enemy', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 217, '2025-09-22', '2025-09-29', false],
            ['Effortless', 'Greg McKeown', BookCategory::NonFiction, 'Productivity', null, 213, '2025-09-30', '2025-10-03', false],
            ['Why Has Nobody Told Me This Before?', 'Julie Smith', BookCategory::NonFiction, 'Psychology & Mental Health', null, 320, '2025-10-04', '2025-10-12', false],
            ['The Software Engineer\'s Guidebook', 'Gergely Orosz', BookCategory::NonFiction, 'Software Engineering', null, 391, '2025-09-01', '2025-10-11', false],
            ['Courage is Calling', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 275, '2025-10-12', '2025-10-17', false],
            ['Wind and Truth', 'Brandon Sanderson', BookCategory::Fiction, 'Epic Fantasy', 'The Stormlight Archive', 1329, '2025-10-01', '2025-10-27', false],
            ['The Subtle Art of Not Giving a F*ck', 'Mark Manson', BookCategory::NonFiction, 'Self-Help', null, 204, '2025-10-17', '2025-10-29', false],
            ['Discipline is Destiny', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 326, '2025-10-29', '2025-11-05', false],
            ['The Comfort Crisis', 'Michael Easter', BookCategory::NonFiction, 'Self-Help & Health', null, 281, '2025-11-05', '2025-11-08', false],
            ['Elantris', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 580, '2025-11-06', '2025-11-19', false],
            ['Right Thing, Right Now', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 338, '2025-11-09', '2025-11-22', false],
            ['Thinking In Systems', 'Donella Meadows', BookCategory::NonFiction, 'Systems Thinking', null, 203, '2025-11-22', '2025-11-28', false],
            ['Project Hail Mary', 'Andy Weir', BookCategory::Fiction, 'Science Fiction', null, 476, '2025-11-23', '2025-12-01', false],
            ['Yumi and the Nightmare Painter', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', 'Hoid\'s Travails', 362, '2025-12-02', '2025-12-14', false],
            ['Dungeon Crawler Carl', 'Matt Dinniman', BookCategory::Fiction, 'LitRPG / Science Fantasy', 'Dungeon Crawler Carl', 427, '2025-12-15', '2025-12-23', false],
            ['The Shadow of What Was Lost', 'James Islington', BookCategory::Fiction, 'Epic Fantasy', 'The Licanius Trilogy', 693, '2025-12-29', '2026-01-04', false],
            ['Thirsty', 'Tom Gilbey', BookCategory::NonFiction, 'Food & Drink', null, 278, '2025-12-27', '2026-01-09', false],
            ['Clear Thinking', 'Shane Parrish', BookCategory::NonFiction, 'Decision Making & Psychology', null, 255, '2025-12-02', '2026-01-10', false],
            ['Digital Minimalism', 'Cal Newport', BookCategory::NonFiction, 'Productivity & Technology', null, 264, '2026-01-14', '2026-01-21', false],
            ['The 48 Laws of Power', 'Robert Greene', BookCategory::NonFiction, 'Psychology & Strategy', null, 430, '2025-11-13', '2026-01-31', false],
            ['An Echo of Things to Come', 'James Islington', BookCategory::Fiction, 'Epic Fantasy', 'The Licanius Trilogy', 716, '2026-01-11', '2026-01-31', false],
            ['The Light of All That Falls', 'James Islington', BookCategory::Fiction, 'Epic Fantasy', 'The Licanius Trilogy', 816, '2026-02-01', '2026-02-15', false],
            ['Isles of the Emberdark', 'Brandon Sanderson', BookCategory::Fiction, 'Fantasy', null, 399, '2026-02-16', '2026-02-26', false],
            ['The Blade Itself', 'Joe Abercrombie', BookCategory::Fiction, 'Grimdark Fantasy', 'The First Law', 515, '2026-02-27', '2026-03-09', false],
            ['Wisdom Takes Work', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 382, '2026-02-16', '2026-04-29', false],
            ['Before They Are Hanged', 'Joe Abercrombie', BookCategory::Fiction, 'Grimdark Fantasy', 'The First Law', 570, '2026-03-10', '2026-04-30', false],
            ['The Way of Excellence', 'Brad Stulberg', BookCategory::NonFiction, 'Self-Help & Performance', null, 259, '2026-05-01', '2026-05-10', false],
            ['Last Argument of Kings', 'Joe Abercrombie', BookCategory::Fiction, 'Grimdark Fantasy', 'The First Law', 670, '2026-04-30', '2026-05-18', false],
            ['Atomic Habits', 'James Clear', BookCategory::NonFiction, 'Productivity & Self-Help', null, 253, '2026-04-30', '2026-05-31', true],
            ['Stillness is the Key', 'Ryan Holiday', BookCategory::NonFiction, 'Stoicism', null, 264, '2026-06-01', '2026-06-14', false],
            ['Hyperion', 'Dan Simmons', BookCategory::Fiction, 'Science Fiction', 'Hyperion Cantos', 473, '2026-05-18', '2026-07-04', false],
            ['The Devils', 'Joe Abercrombie', BookCategory::Fiction, 'Fantasy', null, 560, '2026-07-01', '2026-07-11', false],
            ['Red Rising', 'Pierce Brown', BookCategory::Fiction, 'Science Fiction', 'Red Rising Saga', 382, '2026-07-12', '2026-07-18', false],
            ['Hidden Potential', 'Adam Grant', BookCategory::NonFiction, 'Psychology', null, 242, '2026-07-13', '2026-07-18', false],
            ['Hyperfocus', 'Chris Bailey', BookCategory::NonFiction, 'Productivity', null, 215, '2026-07-18', '2026-07-23', false],
            ['Golden Son', 'Pierce Brown', BookCategory::Fiction, 'Science Fiction', 'Red Rising Saga', 442, '2026-07-18', '2026-07-30', false],
            ['Inside The Box', 'David Epstein', BookCategory::NonFiction, 'Psychology', null, 234, '2026-07-23', '2026-07-28', false],
            ['Morning Star', 'Pierce Brown', BookCategory::Fiction, 'Science Fiction', 'Red Rising Saga', 515, '2026-07-31', '2026-08-11', false],
            ['The Power of Habit', 'Charles Duhigg', BookCategory::NonFiction, 'Productivity & Self-Help', null, 286, '2026-07-31', '2026-08-17', false],
            ['Of Mice and Men', 'John Steinbeck', BookCategory::Fiction, 'Classic Literature', null, 105, '2026-08-27', '2026-08-30', false],
            ['You Can Just Do Things', 'Cate Hall', BookCategory::NonFiction, 'Productivity & Self-Help', null, 192, '2026-08-23', '2026-10-04', false],
            ['Scion', 'James Islington', BookCategory::Fiction, 'Science Fiction', null, 226, '2026-10-03', '2026-10-15', false],
            ['Man\'s Search for Meaning', 'Viktor E Frankl', BookCategory::NonFiction, 'Philosophy & Self-Help', null, 154, '2026-09-03', '2026-10-18', false],
            ['East of Eden', 'John Steinbeck', BookCategory::Fiction, 'Literary Fiction', null, 602, '2026-10-18', null, false],
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: BookCategory, 3: string, 4: ?string, 5: int}>
     */
    private function toBeReadBooks(): array
    {
        return [
            ['"No Offence, But..."', 'Gina Martin', BookCategory::NonFiction, 'Social Commentary & Politics', null, 310],
            ['168 Hours: You Have More Time Than You Think', 'Laura Vanderkam', BookCategory::NonFiction, 'Productivity', null, 238],
            ['7 Habits of Highly Effective People', 'Stephen R Covey', BookCategory::NonFiction, 'Self-Help & Leadership', null, 332],
            ['A Brief History of Black Holes And why Nearly Everything You Know about Them is Wrong', 'Becky Smethurst', BookCategory::NonFiction, 'Physics & Astronomy', null, 264],
            ['A Time for Mercy', 'John Grisham', BookCategory::Fiction, 'Legal Thriller', 'Jake Brigance', 464],
            ['Anatomy of a Breakthrough', 'Adam Alter', BookCategory::NonFiction, 'Psychology & Self-Help', null, 247],
            ["Assassin's Apprentice", 'Robin Hobb', BookCategory::Fiction, 'Epic Fantasy', 'The Farseer Trilogy', 392],
            ['Be the Change A Toolkit for the Activist in You', 'Gina Martin', BookCategory::NonFiction, 'Social Commentary & Politics', null, 283],
            ['Best Served Cold', 'Joe Abercrombie', BookCategory::Fiction, 'Grimdark Fantasy', null, 662],
            ['Black Holes', 'Brian Cox, Jeffrey R. Forshaw', BookCategory::NonFiction, 'Physics & Astronomy', null, 263],
            ["Carl's Doomsday Scenario", 'Matt Dinniman', BookCategory::Fiction, 'LitRPG / Science Fantasy', 'Dungeon Crawler Carl', 351],
            ['City of Last Chances', 'Adrian Tchaikovsky', BookCategory::Fiction, 'Fantasy', 'The Tyrant Philosophers', 498],
            ['Consider Phlebas', 'Iain M. Banks', BookCategory::Fiction, 'Science Fiction', 'Culture', 496],
            ['Crucial Conversations', 'Joseph Grenny, Kerry Patterson, Ron McMillan, Al Switzler, Emily Gregory', BookCategory::NonFiction, 'Communication & Business', null, 268],
            ['Designing Data Intensive Applications', 'Martin Kleppmann & Chris Riccomini', BookCategory::NonFiction, 'Software Engineering', null, 600],
            ['Designing Your Life Build the Perfect Career, Step by Step', 'Bill Burnett, Dave Evans', BookCategory::NonFiction, 'Career & Self-Help', null, 198],
            ['Drive', 'Daniel H. Pink', BookCategory::NonFiction, 'Psychology & Business', null, 215],
            ['Fahrenheit 451', 'Ray Bradbury', BookCategory::Fiction, 'Dystopian Fiction', null, 211],
            ['Feel-Good Productivity', 'Ali Abdaal', BookCategory::NonFiction, 'Productivity', null, 262],
            ['Getting Things Done', 'David Allen', BookCategory::NonFiction, 'Productivity', null, 301],
            ['How to Calm Your Mind Finding Peace and Productivity in Anxious Times', 'Chris Bailey', BookCategory::NonFiction, 'Psychology & Mental Health', null, 232],
            ['How to Change Your Life Lessons on Transformation from the World of High Performance', 'Jake Humphrey, Damian Hughes', BookCategory::NonFiction, 'Self-Help & Performance', null, 220],
            ['How to Do Nothing: Resisting the Attention Economy', 'Jenny Odell', BookCategory::NonFiction, 'Philosophy & Self-Help', null, 204],
            ['How to Win Friends and Influence People', 'Dale Carnegie', BookCategory::NonFiction, 'Self-Help', null, 264],
            ['Intentional', 'Chris Bailey', BookCategory::NonFiction, 'Productivity', null, 229],
            ['Iron Gold', 'Pierce Brown', BookCategory::Fiction, 'Science Fiction', 'Red Rising Saga', 600],
            ['Jade City', 'Fonda Lee', BookCategory::Fiction, 'Fantasy', 'The Green Bone Saga', 495],
            ['Katabasis', 'R. F. Kuang', BookCategory::Fiction, 'Dark Academia Fantasy', null, 541],
            ['Leaders Eat Last', 'Simon Sinek', BookCategory::NonFiction, 'Leadership & Business', null, 305],
            ['Lost Connections', 'Johann Hari', BookCategory::NonFiction, 'Psychology & Mental Health', null, 319],
            ['Make Time', 'Jake Knapp, John Zeratsky', BookCategory::NonFiction, 'Productivity', null, 256],
            ['Malice', 'John Gwynne', BookCategory::Fiction, 'Epic Fantasy', 'The Faithful and the Fallen', 627],
            ['Mastery', 'Robert Greene', BookCategory::NonFiction, 'Psychology & Strategy', null, 311],
            ['Multipliers', 'Liz Wiseman', BookCategory::NonFiction, 'Leadership & Business', null, 284],
            ['Never Split the Difference', 'Chris Voss', BookCategory::NonFiction, 'Negotiation & Business', null, 245],
            ['Nightwatch', 'Terry Pratchett', BookCategory::Fiction, 'Comic Fantasy', 'Discworld', 475],
            ['Night Manager', 'John Le Carré', BookCategory::Fiction, 'Spy Fiction', null, 473],
            ['No Bullsh*t Leadership', 'Chris Hirst', BookCategory::NonFiction, 'Leadership', null, 209],
            ['Radical Candor', 'Kim Scott', BookCategory::NonFiction, 'Leadership & Business', null, 302],
            ['Revenge Of The Tipping Point', 'Malcolm Gladwell', BookCategory::NonFiction, 'Psychology & Business', null, 304],
            ['Scarcity Brain', 'Michael Easter', BookCategory::NonFiction, 'Self-Help & Health', null, 288],
            ['Site Reliability Engineering', 'Betsy Beyer, Chris Jones, Jennifer Petoff & Niall Richard Murphy', BookCategory::NonFiction, 'Software Engineering', null, 524],
            ['Strong Ground', 'Brené Brown', BookCategory::NonFiction, 'Leadership', null, 394],
            ['Supercommunicators', 'Charles Duhigg', BookCategory::NonFiction, 'Psychology & Communication', null, 246],
            ['Surrounded by Idiots', 'Thomas Erikson', BookCategory::NonFiction, 'Psychology & Self-Help', null, 267],
            ['The 4-Hour Work Week: ', 'Tim Ferriss', BookCategory::NonFiction, 'Productivity & Business', null, 376],
            ['The 5 Types of Wealth', 'Sahil Bloom', BookCategory::NonFiction, 'Finance', null, 369],
            ['The Anxious Generation', 'Jonathan Haidt', BookCategory::NonFiction, 'Psychology & Technology', null, 313],
            ['The Black Prism', 'Brent Weeks', BookCategory::Fiction, 'Epic Fantasy', 'Lightbringer', 735],
            ['The Body Keeps the Score', 'Bessel A. Van der Kolk', BookCategory::NonFiction, 'Psychology & Mental Health', null, 428],
            ['The Creative Act', 'Rick Rubin', BookCategory::NonFiction, 'Creativity & Philosophy', null, 415],
            ['The Dose Effect', 'T. J. Power', BookCategory::NonFiction, 'Self-Help & Health', null, 299],
            ["The Dungeon Anarchist's Cookbook", 'Matt Dinniman', BookCategory::Fiction, 'LitRPG / Science Fantasy', 'Dungeon Crawler Carl', 512],
            ['The Exchange', 'John Grisham', BookCategory::Fiction, 'Legal Thriller', 'The Firm', 334],
            ['The Fall of Hyperion', 'Dan Simmons', BookCategory::Fiction, 'Science Fiction', 'Hyperion Cantos', 584],
            ['The Firm', 'John Grisham', BookCategory::Fiction, 'Legal Thriller', 'The Firm', 490],
            ['The Gunslinger', 'Stephen King', BookCategory::Fiction, 'Dark Fantasy', 'The Dark Tower', 238],
            ['The Happiness Hypothesis', 'Jonathan Haidt', BookCategory::NonFiction, 'Psychology', null, 243],
            ['The Happy Index', 'James Timpson', BookCategory::NonFiction, 'Business & Leadership', null, 284],
            ['The Hobbit', 'J. R. R. Tolkien', BookCategory::Fiction, 'Fantasy', 'Middle-earth', 282],
            ['The Infinite Game', 'Simon Sinek', BookCategory::NonFiction, 'Leadership & Business', null, 224],
            ['The Innovation Stack', 'Jim McKelvey', BookCategory::NonFiction, 'Business & Entrepreneurship', null, 261],
            ['The Let Them Theory', 'Mel Robbins', BookCategory::NonFiction, 'Self-Help & Psychology', null, 304],
            ['The Making of a Leader', 'Tom Young', BookCategory::NonFiction, 'Leadership', null, 352],
            ['The Name of the Wind', 'Patrick Rothfuss', BookCategory::Fiction, 'Epic Fantasy', 'The Kingkiller Chronicle', 662],
            ['The Player of Games', 'Iain M. Banks', BookCategory::Fiction, 'Science Fiction', 'Culture', 308],
            ['The Priory of the Orange Tree', 'Samantha Shannon', BookCategory::Fiction, 'Epic Fantasy', null, 804],
            ['The Shadow of the Gods', 'John Gwynne', BookCategory::Fiction, 'Epic Fantasy', 'The Bloodsworn Saga', 478],
            ['The Spy Who Came in From The Cold', 'John le Carré', BookCategory::Fiction, 'Spy Fiction', 'George Smiley', 253],
            ['The Stand', 'Stephen King', BookCategory::Fiction, 'Dark Fantasy', null, 1325, null, null, false],
            ['The Three-Body Problem', 'Cixin Liu', BookCategory::Fiction, 'Science Fiction', "Remembrance of Earth's Past", 424],
            ['The Tipping Point', 'Malcolm Gladwell', BookCategory::NonFiction, 'Psychology & Business', null, 259],
            ['The Will of the Many', 'James Islington', BookCategory::Fiction, 'Epic Fantasy', 'Hierarchy', 684],
            ['This is Marketing', 'Seth Godin', BookCategory::NonFiction, 'Marketing & Business', null, 252],
            ['To Kill a Mockingbird', 'Harper Lee', BookCategory::Fiction, 'Literary Fiction', null, 309],
            ['Tranquility by Tuesday', 'Laura Vanderkam', BookCategory::NonFiction, 'Productivity', null, 246],
            ['Ultra-Processed People', 'Chris van Tulleken', BookCategory::NonFiction, 'Health & Nutrition', null, 330],
            ['Ultralearning', 'Scott H. Young', BookCategory::NonFiction, 'Productivity & Self-Help', null, 257],
            ['Upstream', 'Dan Heath', BookCategory::NonFiction, 'Business & Psychology', null, 244],
            ['Vibe', 'Adam Grant', BookCategory::NonFiction, 'Self-Help & Psychology', null, 253],
            ['What Color Is Your Parachute? 2022 Edition.', 'Richard N. Bolles', BookCategory::NonFiction, 'Career & Self-Help', null, 331],
            ['What You Do Is Who You Are', 'Ben Horowitz', BookCategory::NonFiction, 'Leadership & Business', null, 246],
            ['Zero To One', 'Peter Thiel with Blake Masters', BookCategory::NonFiction, 'Business & Entrepreneurship', null, 195],
        ];
    }

    /**
     * Populate the blog with the posts and reading recaps carried over from
     * the previous iteration of the site. Skipped in testing, where posts are
     * populated via factories instead.
     */
    private function populatePosts(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $now = now();

        $rows = array_map(
            fn(array $post) => [
                'title' => $post[0],
                'slug' => $post[1],
                'type' => $post[2]->value,
                'category' => null,
                'body' => $post[3],
                'status' => $post[4]->value,
                'published_at' => $post[5],
                'starts_at' => $post[6] ?? null,
                'ends_at' => $post[7] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [...$this->legacyPosts(), ...$this->legacyRecaps()],
        );

        DB::table('posts')->insert($rows);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: PostType, 3: string, 4: PostStatus, 5: ?string}>
     */
    private function legacyPosts(): array
    {
        return [
            [
                'Move about your business',
                Str::slug('Move about your business'),
                PostType::Post,
                "These aren't the droids you're looking for...",
                PostStatus::Published,
                '2025-05-09',
            ],
            [
                'Maybe there is something here! Well some books at least...',
                Str::slug('Maybe there is something here! Well some books at least...'),
                PostType::Post,
                'Finally gotten round to adding a "Bookshelf" page, which I\'d seen on other personal sites and really liked the idea of. It\'s really basic at the moment (forgive me, I\'m recovering from Covid), but I\'ve plans to flesh it out to bring more info to each entry. Go take a look...',
                PostStatus::Published,
                '2025-10-14',
            ],
            [
                "Well, I've done it again",
                Str::slug("Well, I've done it again"),
                PostType::Post,
                'Yes another iteration of the website. Maybe this one will stick!',
                PostStatus::Published,
                '2026-10-05',
            ],
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: PostType, 3: string, 4: PostStatus, 5: ?string, 6: string, 7: string}>
     */
    private function legacyRecaps(): array
    {
        return [
            [
                '2026.7',
                '2026-7',
                PostType::Recap,
                <<<'MD'
                Seems I have gotten back into the habit of daily reading now and a good showing for July. Especially helped by the pace and how gripping the fiction books were.

                The books, in order, are:

                - The Devils by Joe Abercrombie
                - Hidden Potential by Adam Grant
                - Red Rising by Pierce Brown
                - Hyperfocus by Chris Bailey
                - Inside the Box by David Epstein
                - Golden Son by Pierce Brown
                MD,
                PostStatus::Draft,
                null,
                '2026-07-01',
                '2026-07-31',
            ],
            [
                '2026 - The First Six Months',
                '2026-the-first-six-months',
                PostType::Recap,
                <<<'MD'
                I have decided to try and post regular recaps of the books I have read over the year. Ideally this would be once a quarter, or monthly if my pace improves. I missed the first quarter of this year, so it will be the first two quarters instead.

                Generally I'll include a book that I finish within the time period, but I'm also going to include the book I just finished, which is technically in this quarter but I read the majority of it over the previous one, so it'll be fine this time.

                ## Books finished this period

                - **The Shadow of What Was Lost** by James Islington (The Licanius Trilogy) — Fiction, Epic Fantasy
                - **Thirsty** by Tom Gilbey — Non-Fiction, Food & Drink
                - **Clear Thinking** by Shane Parrish — Non-Fiction, Decision Making & Psychology
                - **Digital Minimalism** by Cal Newport — Non-Fiction, Productivity & Technology
                - **The 48 Laws of Power** by Robert Greene — Non-Fiction, Psychology & Strategy
                - **An Echo of Things to Come** by James Islington (The Licanius Trilogy) — Fiction, Epic Fantasy
                - **The Light of All That Falls** by James Islington (The Licanius Trilogy) — Fiction, Epic Fantasy
                - **Isles of the Emberdark** by Brandon Sanderson — Fiction, Fantasy
                - **The Blade Itself** by Joe Abercrombie (The First Law) — Fiction, Grimdark Fantasy
                - **Wisdom Takes Work** by Ryan Holiday — Non-Fiction, Stoicism
                - **Before They Are Hanged** by Joe Abercrombie (The First Law) — Fiction, Grimdark Fantasy
                - **The Way of Excellence** by Brad Stulberg — Non-Fiction, Self-Help & Performance
                - **Last Argument of Kings** by Joe Abercrombie (The First Law) — Fiction, Grimdark Fantasy
                - **Atomic Habits** by James Clear — Non-Fiction, Productivity & Self-Help (Reread)
                - **Stillness is the Key** by Ryan Holiday — Non-Fiction, Stoicism
                - **Hyperion** by Dan Simmons (Hyperion Cantos) - Fiction, Science Fiction

                So 16 books for the first half of 2026, compared to 32 for 2025. Some of the difference is due to coming off the back of the end of the Wheel of Time series and into Sanderson's Cosmere allowed me to be pulled forward into new worlds but a big part has been my mindset shift after being made redundant at the end of March.

                ## Fiction

                ### The Licanius Trilogy, James Islington

                The Shadow of What Was Lost, An Echo of Things to Come, The Light of All That Falls

                I picked this up because the blurb had a review saying "Fans of Robert Jordan and Brandon Sanderson will find much to admire."

                Despite it sounding like a slightly backhanded review, I thought that sounds a lot like me. A fun series that blended some time-travel into the usual fantasy fare. Some elements did feel like they'd been lifted from other books... but that can be said for a lot of fantasy that follows Tolkien.

                ### Isles of the Emberdark, Brandon Sanderson

                Set in a future version of his Cosmere universe it felt like a blend of the traditional fantasy alongside some sci-fi elements with a rag-tag crew of a "space"-ship. The two plotlines that came together had me more interested in one of them and I hope he revisits those characters in either a another standalone or a novella.

                ### The First Law Trilogy, Joe Abercrombie

                Bought due to seeing him with Sanderson at events and him being recommended by him. Definitely a contrast when coming off the back of a spree of Cosmere books. Much darker, swearier and featuring a great cast of ne'er-do-wells.

                It is very classical sword and sorcery style and uses some basic archetypes and rogues which you can't help but come to root for, despite them being utter bastards. The plotline isn't overly strong and the conclusion not quite as expected in comparison to other series, but fitting for this series.

                ### Hyperion

                This has seemed to take me an age to read. I wanted a easy standalone sci-fi book after a lot of fantasy but it now seems that you need to read the 2nd book to get the conclusion.

                I don't think it was what I expected and having looked into it, it's meant to be a sci-fi version of the Canterbury Tales. This tale has multiple stories, relating to the main characters, and how they all link to a central thing in the universe.

                Old school sci-fi in it's future building and has references to other classics like Neuromancer.

                The Sci-Fi Masterworks version of book 2 is out in August, so I'll wait until then to pick the series back up.

                ## Non-Fiction

                ### Thirsty, Tom Gilbey

                The madman that ran a marathon whilst doing a taste test of wine at each mile marker. A more thoughtful and informative look at wine and his journey with it. A fun and non-pretentious read. Much more tame than his YouTube channel, which I heartily recommend.

                ### Clear Thinking, Shane Parrish

                Seems that this one just occupied some time. I don't have any notes from it, or clear takeaways.

                ### Digital Minimalism, Cal Newport

                Oddly I had this book on my TBR but it didn't get picked up until after his newer one "Slow Productivity". Some interesting takes on how to reduce your reliance _(read addiction)_ to screens and digital distractions. Some of which I had already encountered due to listening to his podcast.

                ### The 48 Laws of Power, Robert Greene

                A controversial one which I'd pondered whether to read or not. Other authors I'd read suggested it, including Ryan Holiday, so I decided to give it a go and make up my own mind.

                The "Laws" spoken of are just human behaviour and you'll encounter them in life regardless whether you read the book or not. I could see some example within my own experiences with people and perhaps it'll help me spot them going forward.

                ### Wisdom Takes Work, Ryan Holiday

                The fourth, and final, of his Virtues series which continues the theme of looking back across history (both recent and past) and applying the tale to the virtue. An enjoyable read for some of the stories and they're well linked to the virtue. A quote I liked from it:

                > Deeds not words. All the virtues are supposed to be verbs and not nouns; things that you do.

                ### The Way of Excellence, Brad Stulberg

                I read this due to Cal Newport recommending it and hearing him on his podcast. The title sounds more grandiose than the content, which strives to show how it's more the "doing" than the end result that matters. I.e.

                > Excellence is not a destination; it is a process of becoming.

                ### Atomic Habits, James Clear

                A re-read and it's still a bestseller for a reason. A lot of the advice given makes you go "oh yeah, that makes sense" and you should have thought of it before. Some of the actionable parts of it are good to apply to day-to-day life.

                > Your identity emerges out of your habits. Every action is a vote for the type of person you wish to become.

                I think that's a heavy hitting part of the book. We all want to be better, but if we fall back to a bad habit, then that's who we will remain to be.

                ### Stillness is the Key, Ryan Holiday

                An earlier book by Ryan Holiday, which I've perhaps read out of order. It fits alongside his "Ego is the Enemy" and "The Obstacle is the Way". Some good stories in it, like Wisdom Takes Work, which give you an idea of how to approach elements of life.

                I think his range of works is best consumed together and you could condense a lot of the various ideas together for some clear changes to make.
                MD,
                PostStatus::Published,
                '2026-07-01',
                '2026-01-01',
                '2026-06-30',
            ],
        ];
    }
};
