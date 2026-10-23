<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import { ArrowLeftIcon } from '@/components/site/icons';
import Prose from '@/components/site/Prose.vue';
import SeoHead from '@/components/site/SeoHead.vue';
import { usePreviousUrl } from '@/composables/usePreviousUrl';

const props = defineProps<{
    title: string;
    description: string;
    bodyHtml: string;
    backLabel?: string;
    featuredImageUrl?: string | null;
    publishedAt: string;
    updatedAt?: string | null;
}>();

const { previousUrl, goBack } = usePreviousUrl();
</script>

<template>
    <SeoHead
        :title="title"
        :description="description"
        :image="featuredImageUrl"
        :article="{ publishedAt, updatedAt }"
    />
    <Container class="mt-16 lg:mt-32">
        <div class="xl:relative">
            <div class="mx-auto max-w-2xl">
                <button
                    v-if="previousUrl"
                    type="button"
                    :aria-label="backLabel ?? 'Go back'"
                    class="group mb-8 flex h-10 w-10 items-center justify-center rounded-full border border-ash-grey-700/50 bg-espresso-950 shadow-md ring-0 ring-white/10 transition hover:border-ash-grey-700 hover:ring-white/20 lg:absolute lg:-left-5 lg:-mt-2 lg:mb-0 xl:-top-1.5 xl:left-0 xl:mt-0"
                    @click="goBack()"
                >
                    <ArrowLeftIcon
                        class="h-4 w-4 stroke-ash-grey-500 transition group-hover:stroke-ash-grey-400"
                    />
                </button>
                <article>
                    <img
                        v-if="props.featuredImageUrl"
                        :src="props.featuredImageUrl"
                        alt=""
                        class="aspect-video w-full rounded-2xl object-cover"
                    />
                    <header class="flex flex-col">
                        <h1
                            class="mt-6 text-4xl font-bold tracking-tight text-ash-grey-100 sm:text-5xl"
                        >
                            {{ title }}
                        </h1>
                        <slot name="meta" />
                    </header>
                    <Prose class="mt-8">
                        <div v-html="bodyHtml" />
                    </Prose>
                </article>
            </div>
        </div>
    </Container>
</template>
