/** Converts an ISO datetime string (as returned by Eloquent date casts) to a `YYYY-MM-DD` value suitable for a native date input. */
export function toDateInputValue(
    dateString: string | null | undefined,
): string {
    if (!dateString) {
        return '';
    }

    return dateString.slice(0, 10);
}

/** Today's date as a `YYYY-MM-DD` value suitable for a native date input, in the local timezone. */
export function todayDateInputValue(): string {
    const now = new Date();
    const offsetMs = now.getTimezoneOffset() * 60 * 1000;

    return new Date(now.getTime() - offsetMs).toISOString().slice(0, 10);
}
