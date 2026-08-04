export function formatDate(date: string): string {
    const [year, month, day] = date.slice(0, 10).split('-');

    return `${day}/${month}/${year}`;
}

export function todayDateString(): string {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}
