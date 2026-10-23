function format(iso: string): string {
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        timeZone: 'UTC',
    });
}

export function formatDateRange(startsAt: string, endsAt: string): string {
    return `${format(startsAt)} – ${format(endsAt)}`;
}
