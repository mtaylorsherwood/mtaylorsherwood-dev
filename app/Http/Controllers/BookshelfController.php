<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class BookshelfController extends Controller
{
    public function index(): View
    {
        $currently_reading = self::$currently_reading;
        $finished_books = collect(self::$finished_books)->sortBy(callback: 3, descending: true);
        $number_of_books = count($finished_books);
        $number_of_pages = $finished_books->sum('4');

        return view('bookshelf', compact('finished_books', 'currently_reading', 'number_of_books', 'number_of_pages'));
    }

    private static array $currently_reading = [
        ['The Stand', 'Stephen King', '2025-10-29', null, 1325],
        ['Discipline is Destiny', 'Ryan Holiday', '2025-10-29', null, 326],
    ];

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
    ];
}
