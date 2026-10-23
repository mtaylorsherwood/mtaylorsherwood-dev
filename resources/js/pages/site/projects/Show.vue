<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Container from '@/components/site/Container.vue';
import { ArrowLeftIcon, GitHubIcon, LinkIcon } from '@/components/site/icons';
import Prose from '@/components/site/Prose.vue';
import SeoHead from '@/components/site/SeoHead.vue';
import { usePreviousUrl } from '@/composables/usePreviousUrl';
import { show as showPost } from '@/routes/blog';
import { show as showRecap } from '@/routes/recaps';
import type { ProjectPostSummary, PublicProjectDetail } from '@/types';

defineProps<{
    project: PublicProjectDetail;
}>();

const { previousUrl, goBack } = usePreviousUrl();

function postHref(post: ProjectPostSummary) {
    return post.type === 'recap' ? showRecap(post.slug) : showPost(post.slug);
}
</script>

<template>
    <SeoHead
        :title="project.title"
        :description="project.blurb"
        :image="project.images[0]?.url ?? null"
    />

    <Container class="mt-16 lg:mt-32">
        <div class="xl:relative">
            <div class="mx-auto max-w-2xl">
                <button
                    v-if="previousUrl"
                    type="button"
                    aria-label="Go back"
                    class="group mb-8 flex h-10 w-10 items-center justify-center rounded-full border border-ash-grey-700/50 bg-espresso-950 shadow-md ring-0 ring-white/10 transition hover:border-ash-grey-700 hover:ring-white/20 lg:absolute lg:-left-5 lg:-mt-2 lg:mb-0 xl:-top-1.5 xl:left-0 xl:mt-0"
                    @click="goBack()"
                >
                    <ArrowLeftIcon
                        class="h-4 w-4 stroke-ash-grey-500 transition group-hover:stroke-ash-grey-400"
                    />
                </button>

                <article>
                    <header class="flex flex-col">
                        <h1
                            class="mt-6 text-4xl font-bold tracking-tight text-ash-grey-100 sm:text-5xl"
                        >
                            {{ project.title }}
                        </h1>
                        <p class="mt-4 text-base text-ash-grey-400">
                            {{ project.blurb }}
                        </p>

                        <div
                            v-if="project.website_url || project.github_url"
                            class="mt-6 flex items-center gap-6"
                        >
                            <a
                                v-if="project.website_url"
                                :href="project.website_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center gap-2 text-sm font-medium text-ash-grey-200 transition hover:text-spicy-paprika-400"
                            >
                                <LinkIcon class="h-5 w-5 flex-none" />
                                Website
                            </a>
                            <a
                                v-if="project.github_url"
                                :href="project.github_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center gap-2 text-sm font-medium text-ash-grey-200 transition hover:text-spicy-paprika-400"
                            >
                                <GitHubIcon
                                    class="h-5 w-5 flex-none fill-current"
                                />
                                GitHub
                            </a>
                        </div>
                    </header>

                    <div
                        v-if="project.images.length > 0"
                        class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3"
                    >
                        <img
                            v-for="image in project.images"
                            :key="image.id"
                            :src="image.url"
                            alt=""
                            class="aspect-square w-full rounded-xl object-cover"
                        />
                    </div>

                    <Prose class="mt-8">
                        <div v-html="project.body_html" />
                    </Prose>

                    <section v-if="project.posts.length > 0" class="mt-16">
                        <h2
                            class="text-sm font-semibold tracking-wide text-ash-grey-400 uppercase"
                        >
                            Related posts
                        </h2>
                        <div class="mt-6 flex flex-col gap-8">
                            <article
                                v-for="post in project.posts"
                                :key="post.id"
                            >
                                <h3
                                    class="text-base font-semibold text-ash-grey-100"
                                >
                                    <Link
                                        :href="postHref(post)"
                                        class="hover:text-spicy-paprika-400"
                                    >
                                        {{ post.title }}
                                    </Link>
                                </h3>
                                <p class="mt-1 text-sm text-ash-grey-400">
                                    {{ post.excerpt }}
                                </p>
                            </article>
                        </div>
                    </section>
                </article>
            </div>
        </div>
    </Container>
</template>
