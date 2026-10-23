import type { PostType } from './posts';

export type ProjectStatus = 'ongoing' | 'dormant';

export type Project = {
    id: number;
    title: string;
    slug: string;
    status: ProjectStatus;
    blurb: string;
    body: string;
    website_url: string | null;
    github_url: string | null;
};

export type ProjectImage = {
    id: number;
    url: string;
};

export type ProjectWithImages = Project & {
    images: ProjectImage[];
};

export type PublicProjectSummary = {
    id: number;
    title: string;
    slug: string;
    status: ProjectStatus;
    blurb: string;
    website_url: string | null;
    github_url: string | null;
};

export type ProjectPostSummary = {
    id: number;
    title: string;
    slug: string;
    type: PostType;
    excerpt: string;
    published_at: string;
};

export type PublicProjectDetail = {
    title: string;
    slug: string;
    blurb: string;
    body_html: string;
    website_url: string | null;
    github_url: string | null;
    updated_at: string | null;
    images: ProjectImage[];
    posts: ProjectPostSummary[];
};

/** Lightweight project summary for the post form's optional project picker. */
export type PostProjectOption = {
    id: number;
    title: string;
};
