/**
 * Formats a full ISO datetime (as returned for `published_at`) as a plain
 * date, in the reader's local timezone — unlike `formatDate`, which anchors
 * to UTC because it only ever receives date-only (no time) strings.
 */
export function formatPublishedAt(iso: string): string {
    return new Date(iso).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
