<script setup lang="ts">
import PostLayout from '@/components/site/PostLayout.vue';
import { formatDateRange } from '@/lib/formatDateRange';
import type { PublicRecapDetail } from '@/types';

defineProps<{
    recap: PublicRecapDetail;
}>();
</script>

<template>
    <PostLayout
        :title="recap.title"
        :description="recap.excerpt"
        :body-html="recap.body_html"
        :featured-image-url="recap.featured_image_url"
        :published-at="recap.published_at"
        :updated-at="recap.updated_at"
        back-label="Go back to recaps"
    >
        <template #meta>
            <p
                class="order-first flex items-center text-base text-ash-grey-500"
            >
                <span class="h-4 w-0.5 rounded-full bg-ash-grey-500" />
                <span class="ml-3">
                    {{ formatDateRange(recap.starts_at, recap.ends_at) }}
                </span>
            </p>
        </template>
    </PostLayout>
</template>
