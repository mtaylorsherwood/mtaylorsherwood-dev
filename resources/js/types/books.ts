export type BookCategory = 'fiction' | 'non_fiction';

export type Book = {
    id: number;
    title: string;
    author: string;
    category: BookCategory;
    genre: string;
    pages: number;
    series: string | null;
    is_reread: boolean;
    review: string | null;
    started_at: string | null;
    finished_at: string | null;
};

export type BookFilters = {
    year: number | null;
    month: number | null;
    category: BookCategory | null;
    genre: string | null;
    series: string | null;
    author: string | null;
};
