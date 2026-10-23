<script setup lang="ts">
import PostLayout from '@/components/site/PostLayout.vue';
import { formatPublishedAt } from '@/lib/formatPublishedAt';
import type { PublicPostDetail } from '@/types';

defineProps<{
    post: PublicPostDetail;
}>();
</script>

<template>
    <PostLayout
        :title="post.title"
        :description="post.excerpt"
        :body-html="post.body_html"
        :featured-image-url="post.featured_image_url"
        :published-at="post.published_at"
        :updated-at="post.updated_at"
        back-label="Go back to blog"
    >
        <template #meta>
            <time
                :datetime="post.published_at"
                class="order-first flex items-center text-base text-ash-grey-500"
            >
                <span class="h-4 w-0.5 rounded-full bg-ash-grey-500" />
                <span class="ml-3">
                    {{ formatPublishedAt(post.published_at) }}
                </span>
            </time>
        </template>
    </PostLayout>
</template>
