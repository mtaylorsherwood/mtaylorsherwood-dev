import { toDateInputValue } from '@/lib/formatDateInput';
import type { RecapBookSummary } from '@/types';

function categoryLabel(category: RecapBookSummary['category']): string {
    return category === 'fiction' ? 'Fiction' : 'Non-Fiction';
}

/**
 * Builds a markdown list of books finished within [startsAt, endsAt] (inclusive),
 * ordered by finish date, each annotated with its author and tags.
 */
export function generateBookListMarkdown(
    books: RecapBookSummary[],
    startsAt: string,
    endsAt: string,
): string {
    const matches = books
        .filter((book) => {
            const finished = toDateInputValue(book.finished_at);

            return finished >= startsAt && finished <= endsAt;
        })
        .sort((a, b) => a.finished_at.localeCompare(b.finished_at));

    if (matches.length === 0) {
        return '## Books finished this period\n\n_No books finished in this period._\n\n';
    }

    const lines = matches.map((book) => {
        const tags = [categoryLabel(book.category), book.genre].join(', ');
        const series = book.series ? ` (${book.series})` : '';

        return `- **${book.title}** by ${book.author}${series} — ${tags}`;
    });

    return `## Books finished this period\n\n${lines.join('\n')}\n\n`;
}
