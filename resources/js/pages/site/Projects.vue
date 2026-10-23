<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardDescription, CardTitle } from '@/components/site/card';
import SeoHead from '@/components/site/SeoHead.vue';
import SimpleLayout from '@/components/site/SimpleLayout.vue';
import { show } from '@/routes/projects';
import type { PublicProjectSummary } from '@/types';

const props = defineProps<{
    projects: PublicProjectSummary[];
}>();

const ongoing = computed(() =>
    props.projects.filter((project) => project.status === 'ongoing'),
);
const dormant = computed(() =>
    props.projects.filter((project) => project.status === 'dormant'),
);
</script>

<template>
    <SeoHead
        title="Projects"
        description="Martyn's projects and and hobbies."
    />

    <SimpleLayout
        title="Projects, Hobbies and Paraphernalia"
        intro="Some of the things I'm either working on, or have worked on. Likely relating to the garden, the small human or electronics."
    >
        <div class="flex flex-col gap-16">
            <section>
                <h2
                    class="text-sm font-semibold tracking-wide text-ash-grey-400 uppercase"
                >
                    Ongoing
                </h2>

                <p
                    v-if="ongoing.length === 0"
                    class="mt-4 text-sm text-ash-grey-400"
                >
                    I don’t have an active project underway right now, it’s
                    likely life &amp; family is taking my focus.
                </p>

                <ul
                    v-else
                    role="list"
                    class="mt-6 grid grid-cols-1 gap-x-12 gap-y-16 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="project in ongoing"
                        :key="project.slug"
                        as="li"
                    >
                        <CardTitle :href="show(project.slug)">
                            {{ project.title }}
                        </CardTitle>
                        <CardDescription>{{ project.blurb }}</CardDescription>
                    </Card>
                </ul>
            </section>

            <section v-if="dormant.length > 0">
                <h2
                    class="text-sm font-semibold tracking-wide text-ash-grey-400 uppercase"
                >
                    Dormant
                </h2>

                <ul
                    role="list"
                    class="mt-6 grid grid-cols-1 gap-x-12 gap-y-16 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="project in dormant"
                        :key="project.slug"
                        as="li"
                    >
                        <CardTitle :href="show(project.slug)">
                            {{ project.title }}
                        </CardTitle>
                        <CardDescription>{{ project.blurb }}</CardDescription>
                    </Card>
                </ul>
            </section>
        </div>
    </SimpleLayout>
</template>
