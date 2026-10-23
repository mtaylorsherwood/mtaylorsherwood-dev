/**
 * Converts an ISO datetime string (UTC, as returned by the server) to the
 * `YYYY-MM-DDTHH:mm` format a native `datetime-local` input expects, using
 * the browser's local timezone — since that's what the person scheduling a
 * publish date actually sees and means.
 */
export function isoToLocalDateTimeInput(iso: string): string {
    const date = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

/**
 * Converts a `datetime-local` input's timezone-naive value back to a proper
 * UTC ISO string, interpreting it as the browser's local time (matching how
 * `isoToLocalDateTimeInput` displayed it).
 */
export function localDateTimeInputToIso(value: string): string {
    return new Date(value).toISOString();
}
