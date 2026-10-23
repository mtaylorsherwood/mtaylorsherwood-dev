<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { categoryLabel } from '@/lib/categoryLabel';
import { formatDateRange } from '@/lib/formatDateRange';
import { postTypeLabel } from '@/lib/postTypeLabel';
import { dashboard } from '@/routes';
import postRoutes from '@/routes/dashboard/posts';
import type { Paginated, Post } from '@/types';

defineProps<{
    posts: Paginated<Post>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Posts', href: postRoutes.index() },
        ],
    },
});

function statusLabel(post: Post): string {
    if (post.status !== 'published') {
        return 'Draft';
    }

    return post.published_at && new Date(post.published_at) > new Date()
        ? 'Scheduled'
        : 'Published';
}

function destroy(post: Post) {
    if (!confirm(`Delete "${post.title}"? This can't be undone.`)) {
        return;
    }

    router.delete(postRoutes.destroy(post.id).url);
}
</script>

<template>
    <Head title="Posts" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Posts"
                description="Blog posts, reading recaps, and garden updates, drafted and published."
            />
            <Button as-child>
                <Link :href="postRoutes.create()">New post</Link>
            </Button>
        </div>

        <p v-if="posts.data.length === 0" class="text-sm text-muted-foreground">
            You haven’t written any posts yet.
        </p>

        <div v-else class="divide-y divide-border rounded-lg border">
            <div
                v-for="post in posts.data"
                :key="post.id"
                class="flex flex-wrap items-center justify-between gap-4 p-4"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="truncate font-medium">{{ post.title }}</p>
                        <Badge variant="outline">{{
                            postTypeLabel(post.type)
                        }}</Badge>
                        <Badge v-if="post.category" variant="secondary">
                            {{ categoryLabel(post.category) }}
                        </Badge>
                        <Badge
                            :variant="
                                statusLabel(post) === 'Published'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ statusLabel(post) }}
                        </Badge>
                    </div>
                    <p
                        v-if="
                            post.type === 'recap' &&
                            post.starts_at &&
                            post.ends_at
                        "
                        class="text-sm text-muted-foreground"
                    >
                        {{ formatDateRange(post.starts_at, post.ends_at) }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="postRoutes.edit(post.id)">Edit</Link>
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="destroy(post)"
                        >Delete</Button
                    >
                </div>
            </div>
        </div>

        <Pagination :links="posts.links" />
    </div>
</template>
