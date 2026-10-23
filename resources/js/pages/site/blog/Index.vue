<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import {
    Card,
    CardCta,
    CardDescription,
    CardEyebrow,
    CardTitle,
} from '@/components/site/card';
import SeoHead from '@/components/site/SeoHead.vue';
import SimpleLayout from '@/components/site/SimpleLayout.vue';
import { Badge } from '@/components/ui/badge';
import { formatPublishedAt } from '@/lib/formatPublishedAt';
import { postTypeLabel } from '@/lib/postTypeLabel';
import { show as showPost } from '@/routes/blog';
import { show as showRecap } from '@/routes/recaps';
import type { Paginated, PublicPostSummary } from '@/types';

defineProps<{
    posts: Paginated<PublicPostSummary>;
}>();

function href(post: PublicPostSummary) {
    return post.type === 'recap' ? showRecap(post.slug) : showPost(post.slug);
}
</script>

<template>
    <SeoHead
        title="Blog"
        description="My general musings, garden updates, reading recaps and project updates."
    />

    <SimpleLayout
        title="The Blog"
        intro="General thoughts, garden updates, reading recaps and project updates."
    >
        <div class="md:border-l md:border-ash-grey-700/40 md:pl-6">
            <div class="flex max-w-3xl flex-col space-y-16">
                <article
                    v-for="post in posts.data"
                    :key="post.slug"
                    class="md:grid md:grid-cols-4 md:items-baseline"
                >
                    <Card class="md:col-span-3">
                        <img
                            v-if="post.featured_image_url"
                            :src="post.featured_image_url"
                            alt=""
                            class="mb-4 aspect-video w-full rounded-xl object-cover"
                        />
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge
                                v-if="post.type !== 'post'"
                                variant="outline"
                            >
                                {{ postTypeLabel(post.type) }}
                            </Badge>
                        </div>
                        <CardTitle :href="href(post)">
                            {{ post.title }}
                        </CardTitle>
                        <CardEyebrow
                            as="time"
                            :datetime="post.published_at"
                            class="md:hidden"
                            decorate
                        >
                            {{ formatPublishedAt(post.published_at) }}
                        </CardEyebrow>
                        <CardDescription>{{ post.excerpt }}</CardDescription>
                        <CardCta
                            >Read
                            {{
                                postTypeLabel(post.type).toLowerCase()
                            }}</CardCta
                        >
                    </Card>
                    <CardEyebrow
                        as="time"
                        :datetime="post.published_at"
                        class="mt-1 max-md:hidden"
                    >
                        {{ formatPublishedAt(post.published_at) }}
                    </CardEyebrow>
                </article>
            </div>

            <Pagination :links="posts.links" class="mt-16" />
        </div>
    </SimpleLayout>
</template>
