export type PostType = 'post' | 'recap' | 'garden_update' | 'project_update';
export type PostStatus = 'draft' | 'published';
export type PostCategory = 'general' | 'life' | 'tech';

export type Post = {
    id: number;
    title: string;
    slug: string;
    type: PostType;
    category: PostCategory | null;
    featured_image_id: number | null;
    featured_image: { id: number; url: string } | null;
    project_id: number | null;
    body: string;
    status: PostStatus;
    published_at: string | null;
    starts_at: string | null;
    ends_at: string | null;
};

export type PublicPostSummary = {
    id: number;
    title: string;
    slug: string;
    type: PostType;
    category: PostCategory | null;
    excerpt: string;
    published_at: string;
    featured_image_url: string | null;
};

export type PublicPostDetail = {
    title: string;
    slug: string;
    excerpt: string;
    published_at: string;
    updated_at: string | null;
    body_html: string;
    featured_image_url: string | null;
};
