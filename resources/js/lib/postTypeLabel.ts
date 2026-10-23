import type { PostType } from '@/types';

const LABELS: Record<PostType, string> = {
    post: 'Post',
    recap: 'Reading Recap',
    garden_update: 'Garden Update',
    project_update: 'Project Update',
};

/** Human-readable label for a post type. */
export function postTypeLabel(type: PostType): string {
    return LABELS[type];
}
