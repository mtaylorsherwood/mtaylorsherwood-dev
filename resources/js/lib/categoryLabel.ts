import type { PostCategory } from '@/types';

/** Human-readable label for a post category. */
export function categoryLabel(category: PostCategory): string {
    return {
        general: 'General',
        life: 'Life',
        tech: 'Tech',
    }[category];
}
