<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class BookshelfController extends Controller
{
    public function index(): View
    {
        $currently_reading = self::$currently_reading;
        $finished_books = collect(self::$finished_books)->sortBy(callback: '3', descending: true);
        $number_of_books = count($finished_books);
        $number_of_pages = $finished_books->sum('4');
        $finished_by_year = collect(self::$finished_books)
            ->groupBy(function (array $book): int {
                return (int) Carbon::createFromFormat('Y-m-d', $book[3])?->format('Y');
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'pages' => $group->sum(fn (array $b) => $b[4]),
                ];
            })
            ->sortKeysDesc()
            ->toArray();
        $to_be_read = collect(self::$to_be_read);
        $number_to_be_read = $to_be_read->count();
        $number_of_to_be_read_pages = $to_be_read->sum('4');

        return view('bookshelf', compact('finished_books', 'currently_reading', 'number_of_books', 'number_of_pages', 'to_be_read', 'finished_by_year', 'number_to_be_read', 'number_of_to_be_read_pages'));
    }

    /**
     * @var array<int, array{0:string,1:string,2:string|null,3:null,4:int}>
     */
    private static array $to_be_read = [
        ['Radical Candor', 'Kim Scott', '2025-12-01', null, 302],
        ['Atomic Habits', 'James Clear', '2026-01-01', null, 253],
        ['Lost Connections', 'Johann Hari', '2025-01-01', null, 319],
        ['Consider Phlebas', 'Iain M. Banks', '2026-01-01', null, 496],
        ['Digital Minimalism', 'Cal Newport', '2026-01-01', null, 264],
        ['Wisdom Takes Work', 'Ryan Holiday', '2026-01-01', null, 382],
        ['"No Offence, But..."', 'Gina Martin', null, null, 310],
        ['168 Hours: You Have More Time Than You Think', 'Laura Vanderkam', null, null, 238],
        ['7 Habits of Highly Effective People', 'Stephen R Covey', null, null, 332],
        ['A Brief History of Black Holes And why Nearly Everything You Know about Them is Wrong', 'Becky Smethurst', null, null, 264],
        ['A Time for Mercy', 'John Grisham', null, null, 464],
        ['ANATOMY OF A BREAKTHROUGH', 'Adam Alter', null, null, 247],
        ['Assassin\'s Apprentice', 'Robin Hobb', null, null, 392],
        ['Be the Change A Toolkit for the Activist in You', 'Gina Martin', null, null, 283],
        ['Black Holes', 'Brian Cox, Jeffrey R. Forshaw', null, null, 263],
        ['Crucial Conversations', 'Joseph Grenny, Kerry Patterson, Ron McMillan, Al Switzler, Emily Gregory', null, null, 268],
        ['Designing Your Life Build the Perfect Career, Step by Step', 'Bill Burnett, Dave Evans', null, null, 198],
        ['Drive', 'Daniel H. Pink', null, null, 215],
        ['Feel-Good Productivity', 'Ali Abdaal', null, null, 262],
        ['Getting Things Done', 'David Allen', null, null, 301],
        ['Hidden Potential', 'Adam Grant', null, null, 242],
        ['How to Calm Your Mind Finding Peace and Productivity in Anxious Times', 'Chris Bailey', null, null, 232],
        ['How to Change Your Life Lessons on Transformation from the World of High Performance', 'Jake Humphrey, Damian Hughes', null, null, 220],
        ['How to Do Nothing: Resisting the Attention Economy', 'Jenny Odell', null, null, 204],
        ['How to Win Friends and Influence People', 'Dale Carnegie', null, null, 264],
        ['Hyperfocus: How to Work Less to Achieve More', 'Chris Bailey', null, null, 215],
        ['Hyperion', 'Dan Simmons', null, null, 473],
        ['Katabasis', 'R. F. Kuang', null, null, 541],
        ['Leaders Eat Last', 'Simon Sinek', null, null, 305],
        ['Make Time', 'Jake Knapp, John Zeratsky', null, null, 256],
        ['Malice', 'John Gwynne', null, null, 627],
        ['Mastery', 'Robert Greene', null, null, 311],
        ['Multipliers', 'Liz Wiseman', null, null, 284],
        ['Never Split the Difference', 'Chris Voss', null, null, 245],
        ['Night Manager', 'John Le Carré', null, null, 473],
        ['No Bullsh*t Leadership', 'Chris Hirst', null, null, 209],
        ['Red Rising', 'Pierce Brown', null, null, 382],
        ['Revenge Of The Tipping Point', 'Malcolm Gladwell', null, null, 304],
        ['Scarcity Brain', 'Michael Easter', null, null, 288],
        ['Strong Ground', 'Brené Brown', null, null, 394],
        ['Supercommunicators', 'Charles Duhigg', null, null, 246],
        ['Surrounded by Idiots', 'Thomas Erikson', null, null, 267],
        ['The 4-Hour Work Week: ', 'Tim Ferriss', null, null, 376],
        ['The 5 Types of Wealth', 'Sahil Bloom', null, null, 369],
        ['The Blade Itself', 'Joe Abercrombie', null, null, 515],
        ['The Body Keeps the Score', 'Bessel A. Van der Kolk', null, null, 428],
        ['The Exchange', 'John Grisham', null, null, 334],
        ['The Firm', 'John Grisham', null, null, 490],
        ['The Gunslinger', 'Stephen King', null, null, 238],
        ['The Happiness Hypothesis', 'Jonathan Haidt', null, null, 243],
        ['The Happy Index', 'James Timpson', null, null, 284],
        ['The Infinite Game', 'Simon Sinek', null, null, 224],
        ['The Innovation Stack', 'Jim McKelvey', null, null, 261],
        ['The Let Them Theory', 'Mel Robbins', null, null, 304],
        ['The Making of a Leader', 'Tom Young', null, null, 352],
        ['The Player of Games', 'Iain M. Banks', null, null, 308],
        ['The Power of Habit', 'Charles Duhigg', null, null, 286],
        ['The Priory of the Orange Tree', 'Samantha Shannon', null, null, 804],
        ['The Shadow of the Gods', 'John Gwynne', null, null, 478],
        ['The Shadow of What Was Lost', 'James Islington', null, null, 693],
        ['The Spy Who Came in From The Cold', 'John le Carré', null, null, 253],
        ['The Stand', 'Stephen King', null, null, 1325],
        ['The Three-Body Problem', 'Cixin Liu', null, null, 424],
        ['The Tipping Point', 'Malcolm Gladwell', null, null, 259],
        ['The Will of the Many', 'James Islington', null, null, 684],
        ['This is Marketing', 'SETH GODIN', null, null, 252],
        ['To Kill a Mockingbird', 'Harper Lee', null, null, 309],
        ['Tranquility by Tuesday', 'Laura Vanderkam', null, null, 246],
        ['Ultra-Processed People', 'Chris van Tulleken', null, null, 330],
        ['Ultralearning', 'Scott H. Young', null, null, 257],
        ['Upstream', 'Dan Heath', null, null, 244],
        ['What Color Is Your Parachute? 2022 Edition.', 'Richard N. Bolles', null, null, 331],
        ['What You Do Is Who You Are', 'Ben Horowitz', null, null, 246],
        ['Zero To One', 'Peter Thiel with Blake Masters', null, null, 195],
    ];

    /**
     * @var array<int, array{0:string,1:string,2:string,3:null,4:int}>
     */
    private static array $currently_reading = [
        ['Clear Thinking', 'Shane Parrish', '2026-01-02', null, 255],
    ];

    /**
     * @var array<int, array{0:string,1:string,2:string,3:string,4:int}>
     */
    private static array $finished_books = [
        ['The Eye of the World', 'Robert Jordan', '2021-06-01', '2021-06-30', 753],
        ['The Great Hunt', 'Robert Jordan', '2021-07-01', '2021-07-31', 721],
        ['The Dragon Reborn', 'Robert Jordan', '2021-08-01', '2021-08-31', 624],
        ['The Shadow Rising', 'Robert Jordan', '2021-09-01', '2021-09-30', 1051],
        ['The Fires of Heaven', 'Robert Jordan', '2021-10-01', '2021-10-31', 926],
        ['Lord of Chaos', 'Robert Jordan', '2023-04-01', '2023-04-30', 1049],
        ['Call for the Dead', 'John \'le Carré', '2023-11-01', '2024-04-23', 150],
        ['The Frugal Wizard\'s Handbook for Surviving Medieval England', 'Brandon Sanderson', '2024-04-25', '2024-06-10', 364],
        ['So Good They Can\'t Ignore You', 'Cal Newport', '2024-06-11', '2024-06-14', 230],
        ['Mistborn', 'Brandon Sanderson', '2024-06-16', '2024-06-21', 647],
        ['Deep Work', 'Cal Newport', '2024-06-23', '2024-06-28', 263],
        ['The Well of Ascension', 'Brandon Sanderson', '2024-06-23', '2024-08-23', 786],
        ['The Hero of Ages', 'Brandon Sanderson', '2024-08-23', '2024-08-30', 748],
        ['A Crown Of Swords', 'Robert Jordan', '2024-08-31', '2024-09-25', 752],
        ['The Path Of Daggers', 'Robert Jordan', '2024-09-27', '2024-12-21', 669],
        ['Meditations for Mortals', 'Oliver Burkeman', '2024-11-03', '2024-12-26', 169],
        ['The Staff Engineer\'s Path', 'Tanya Reilly', '2024-11-03', '2024-12-30', 311],
        ['The Obstacle is the Way', 'Ryan Holiday', '2024-12-11', '2024-12-25', 192],
        ['Winter\'s Heart', 'Robert Jordan', '2024-12-22', '2025-01-05', 705],
        ['Slow Productivity', 'Cal Newport', '2025-01-01', '2025-01-06', 222],
        ['Crossroads Of Twilight', 'Robert Jordan', '2025-01-05', '2025-01-21', 688],
        ['Clean Code', 'Robert C. Martin', '2025-01-06', '2025-01-29', 314],
        ['Start With Why', 'Simon Sinek', '2025-01-06', '2025-01-29', 225],
        ['Knife Of Dreams', 'Robert Jordan', '2025-01-21', '2025-02-15', 862],
        ['Give and Take', 'Adam Grant', '2025-01-29', '2025-02-23', 315],
        ['The DevOps Handbook', 'Gene Kim, Jez Humble, Patrick Debois, John Willis, Nicole Forsgren', '2025-01-30', '2025-02-25', 421],
        ['The Gathering Storm', 'Robert Jordan, Brandon Sanderson', '2025-02-15', '2025-02-26', 785],
        ['Four Thousand Weeks', 'Oliver Burkeman', '2025-02-23', '2025-02-28', 245],
        ['Towers Of Midnight', 'Robert Jordan, Brandon Sanderson', '2025-03-01', '2025-03-12', 958],
        ['Staff Engineer: Leadership beyond the management track', 'Will Larson', '2025-03-02', '2025-03-31', 318],
        ['Conflicted: Why Arguments Are Tearing Us Apart and How They Can Bring Us Together', 'Ian Leslie', '2025-03-02', '2025-03-18', 257],
        ['The Mom Test: How to talk to customers & learn if your business is a good idea when everyone is lying to you', 'Rob Fitzpatrick', '2025-03-09', '2025-03-31', 129],
        ['A Memory Of Light', 'Robert Jordan, Brandon Sanderson', '2025-03-15', '2025-03-23', 1010],
        ['Warbreaker', 'Brandon Sanderson', '2025-03-25', '2025-03-31', 652],
        ['Tress of the Emerald Sea', 'Brandon Sanderson', '2025-03-31', '2025-04-03', 365],
        ['The Pragmatic Programmer: Your Journey To Mastery, 20th Anniversary Edition (2nd Edition)', 'David Thomas, Andrew Hunt', '2025-04-01', '2025-04-11', 287],
        ['Good Inside', 'Becky Kennedy', '2025-04-01', '2025-04-23', 298],
        ['The Way of Kings', 'Brandon Sanderson', '2025-04-05', '2025-05-21', 1008],
        ['Tiny Experiments', 'Anne-Laure, \'Le Cunff', '2025-04-12', '2025-04-17', 246],
        ['The Hard Thing About Hard Things: Building a Business When There Are No Easy Answers', 'Ben Horowitz', '2025-05-12', '2025-05-19', 275],
        ['The Clean Coder: A Code of Conduct for Professional Programmers', 'Robert C. Martin', '2025-05-12', '2025-05-19', 204],
        ['Building a Second Brain', 'Tiago Forte', '2025-05-21', '2025-05-25', 262],
        ['Don\'t Believe Everything You Think (Expanded Edition): Why Your Thinking Is The Beginning & End Of Suffering', 'Joseph Nguyen', '2025-05-25', '2025-05-27', 171],
        ['THE PARA METHOD', 'Tiago Forte', '2025-05-25', '2025-05-25', 206],
        ['The Psychology of Money: Timeless lessons on wealth, greed, and happiness', 'Morgan Housel', '2025-05-25', '2025-05-27', 238],
        ['The Simple Path to Wealth: Your road map to financial independence and a rich, free life', 'J.L. Collins', '2025-05-27', '2025-05-31', 305],
        ['Words Of Radiance', 'Brandon Sanderson', '2025-06-01', '2025-06-26', 1216],
        ['Refactoring', 'Martin Fowler', '2025-06-01', '2025-07-13', 404],
        ['Dare to Lead', 'Brené Brown', '2025-06-01', '2025-06-13', 272],
        ['Kill It with Fire: Manage Aging Computer Systems (and Future Proof Modern Ones)', 'Marianne Bellotti', '2025-06-10', '2025-06-18', 233],
        ['Tidy First?: A Personal Exercise in Empirical Software Design', 'Kent Beck', '2025-06-16', '2025-07-04', 92],
        ['Edgedancer', 'Brandon Sanderson', '2025-06-28', '2025-06-29', 132],
        ['Alloy Of Law', 'Brandon Sanderson', '2025-06-29', '2025-07-02', 325],
        ['Shadows of Self', 'Brandon Sanderson', '2025-07-02', '2025-07-07', 376],
        ['The Almanack of Naval Ravikant: A Guide to Wealth and Happiness', 'Eric Jorgenson', '2025-07-03', '2025-07-07', 242],
        ['Bands Of Mourning', 'Brandon Sanderson', '2025-07-07', '2025-07-12', 437],
        ['Essentialism: The Disciplined Pursuit of Less', 'Greg McKeown', '2025-07-07', '2025-07-12', 246],
        ['Mistborn Secret History', 'Brandon Sanderson', '2025-07-12', '2025-07-12', 169],
        ['Neuromancer', 'William Gibson', '2025-07-14', '2025-07-28', 297],
        ['Observability Engineering: Achieving Production Excellence', 'Charity Majors, Liz Fong-Jones, George Miranda', '2025-07-14', '2025-08-23', 285],
        ['Mindset', 'Carol Dweck', '2025-07-14', '2025-07-27', 264],
        ['The Emperor\'s Soul', 'Brandon Sanderson', '2025-07-29', '2025-07-31', 103],
        ['Oathbringer', 'Brandon Sanderson', '2025-08-01', '2025-08-19', 1379],
        ['Today Was Fun: A Book About Work (Seriously)', 'Bree Groff', '2025-08-04', '2025-08-09', 260],
        ['Range: Why Generalists Triumph in a Specialized World', 'David Epstein', '2025-08-10', '2025-08-24', 292],
        ['Dawnshard', 'Brandon Sanderson', '2025-08-20', '2025-08-23', 247],
        ['Rhythm of War', 'Brandon Sanderson', '2025-08-24', '2025-09-08', 1372],
        ['Originals: How Non-Conformists Move the World', 'Adam Grant', '2025-08-24', '2025-09-04', 244],
        ['The Lost Metal', 'Brandon Sanderson', '2025-09-10', '2025-09-17', 500],
        ['Think Again', 'Adam Grant', '2025-09-11', '2025-09-27', 297],
        ['The Sunlit Man', 'Brandon Sanderson', '2025-09-17', '2025-09-20', 381],
        ['Ego is the Enemy', 'Ryan Holiday', '2025-09-22', '2025-09-29', 217],
        ['Effortless', 'Greg McKeown', '2025-09-30', '2025-10-03', 213],
        ['Why Has Nobody Told Me This Before?', 'Julie Smith', '2025-10-04', '2025-10-12', 320],
        ['The Software Engineer\'s Guidebook', 'Gergely Orosz', '2025-09-01', '2025-10-11', 391],
        ['Courage is Calling', 'Ryan Holiday', '2025-10-12', '2025-10-17', 275],
        ['Wind and Truth', 'Brandon Sanderson', '2025-10-01', '2025-10-27', 1329],
        ['The Subtle Art of Not Giving a F*ck', 'Mark Manson', '2025-10-17', '2025-10-29', 204],
        ['Discipline is Destiny', 'Ryan Holiday', '2025-10-29', '2025-11-05', 326],
        ['The Comfort Crisis', 'Michael Easter', '2025-11-05', '2025-11-08', 281],
        ['Elantris', 'Brandon Sanderson', '2025-11-06', '2025-11-19', 580],
        ['Right Thing, Right Now', 'Ryan Holiday', '2025-11-09', '2025-11-22', 338],
        ['Thinking In Systems', 'Donella Meadows', '2025-11-22', '2025-11-28', 203],
        ['Project Hail Mary', 'Andy Weir', '2025-11-23', '2025-12-01', 476],
        ['Yumi and the Nightmare Painter', 'Brandon Sanderson', '2025-12-02', '2025-12-14', 362],
        ['Dungeon Crawler Carl', 'Matt Dinniman', '2025-12-15', '2025-12-23', 427],
    ];
}
