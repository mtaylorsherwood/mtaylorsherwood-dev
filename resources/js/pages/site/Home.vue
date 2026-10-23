<script setup lang="ts">
import {
    Card,
    CardCta,
    CardDescription,
    CardEyebrow,
    CardTitle,
} from '@/components/site/card';
import Container from '@/components/site/Container.vue';
import { GitHubIcon, LinkedInIcon } from '@/components/site/icons';
import SeoHead from '@/components/site/SeoHead.vue';
import { Badge } from '@/components/ui/badge';
import { formatPublishedAt } from '@/lib/formatPublishedAt';
import { postTypeLabel } from '@/lib/postTypeLabel';
import { site } from '@/lib/site';
import { show as showPost } from '@/routes/blog';
import { show as showRecap } from '@/routes/recaps';
import type { PublicPostSummary } from '@/types';

defineProps<{
    posts: PublicPostSummary[];
}>();

const socialLinks = [
    { href: site.social.github, label: 'Follow on GitHub', icon: GitHubIcon },
    {
        href: site.social.linkedIn,
        label: 'Follow on LinkedIn',
        icon: LinkedInIcon,
    },
];

function href(post: PublicPostSummary) {
    return post.type === 'recap' ? showRecap(post.slug) : showPost(post.slug);
}
</script>

<template>
    <SeoHead
        :title="site.author"
        description="A personal site by Martyn Taylor-Sherwood. A place for random thoughts, book reviews, garden updates and maybe a sprinkling of software engineering."
    />

    <Container class="mt-9">
        <div class="max-w-2xl">
            <h1
                class="text-4xl font-bold tracking-tight text-ash-grey-100 sm:text-5xl"
            >
                Welcome to my little corner of the internet.
            </h1>
            <p class="mt-6 text-base text-ash-grey-400">
                It's great to have you here.
            </p>
            <p class="mt-3 text-base text-ash-grey-400">
                This is a place for my random thoughts, book reviews, garden updates and maybe a sprinkling of software engineering.
            </p>
            <div class="mt-6 flex gap-6">
                <a
                    v-for="link in socialLinks"
                    :key="link.label"
                    :href="link.href"
                    :aria-label="link.label"
                    class="group -m-1 p-1"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <component
                        :is="link.icon"
                        class="h-6 w-6 fill-ash-grey-400 transition group-hover:fill-ash-grey-300"
                    />
                </a>
            </div>
        </div>
    </Container>

    <Container class="mt-24 md:mt-28">
        <div class="mx-auto max-w-xl lg:max-w-2xl">
            <div class="flex flex-col gap-16">
                <Card v-for="post in posts" :key="post.slug" as="article">
                    <div
                        v-if="post.type !== 'post'"
                        class="flex flex-wrap items-center gap-2"
                    >
                        <Badge variant="outline">
                            {{ postTypeLabel(post.type) }}
                        </Badge>
                    </div>
                    <CardTitle :href="href(post)">
                        {{ post.title }}
                    </CardTitle>
                    <CardEyebrow
                        as="time"
                        :datetime="post.published_at"
                        decorate
                    >
                        {{ formatPublishedAt(post.published_at) }}
                    </CardEyebrow>
                    <CardDescription>{{ post.excerpt }}</CardDescription>
                    <CardCta
                        >Read
                        {{ postTypeLabel(post.type).toLowerCase() }}</CardCta
                    >
                </Card>
            </div>
        </div>
    </Container>
</template>
