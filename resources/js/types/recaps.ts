import type { BookCategory } from './books';

/** Lightweight book projection used for client-side "generate book list" only. */
export type RecapBookSummary = {
    id: number;
    title: string;
    author: string;
    category: BookCategory;
    genre: string;
    series: string | null;
    finished_at: string;
};

export type PublicRecapSummary = {
    id: number;
    title: string;
    slug: string;
    starts_at: string;
    ends_at: string;
    published_at: string;
};

export type PublicRecapDetail = {
    title: string;
    slug: string;
    excerpt: string;
    starts_at: string;
    ends_at: string;
    published_at: string;
    updated_at: string | null;
    body_html: string;
    featured_image_url: string | null;
};
