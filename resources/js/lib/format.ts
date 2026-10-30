/**
 * Times are shown in the office's time zone (APP_TIMEZONE), not the browser's.
 */
export const timeZone: string =
    import.meta.env.VITE_APP_TIMEZONE || 'Asia/Jakarta';

const dateTime = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone,
});
const dateOnly = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeZone: 'UTC',
});
const inputParts = new Intl.DateTimeFormat('en-CA', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
});

export function formatDateTime(value: string | null | undefined): string {
    return value ? dateTime.format(new Date(value)) : '—';
}

/**
 * Calendar dates (birth dates) carry no time of day, so they are read as UTC to avoid shifting a day.
 */
export function formatDate(value: string | null | undefined): string {
    return value ? dateOnly.format(new Date(value.slice(0, 10))) : '—';
}

/**
 * The value a datetime-local input expects, as wall-clock time in the app's time zone.
 */
export function toDateTimeLocal(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    const parts = Object.fromEntries(
        inputParts
            .formatToParts(new Date(value))
            .map((part) => [part.type, part.value]),
    );

    return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
}

export function toDateInput(value: string | null | undefined): string {
    return value ? value.slice(0, 10) : '';
}

export function humanize(value: string): string {
    return value
        .replace(/_/g, ' ')
        .replace(/^\w/, (letter) => letter.toUpperCase());
}
