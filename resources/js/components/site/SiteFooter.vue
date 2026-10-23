<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ContainerInner from '@/components/site/ContainerInner.vue';
import ContainerOuter from '@/components/site/ContainerOuter.vue';
import { siteFooterNavItems } from '@/components/site/navigation';
import { site } from '@/lib/site';
import { feed } from '@/routes';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="mt-32 flex-none">
        <ContainerOuter>
            <div class="border-t border-ash-grey-700/40 pt-10 pb-16">
                <ContainerInner>
                    <div
                        class="flex flex-col items-center justify-between gap-6 md:flex-row"
                    >
                        <div
                            class="flex flex-wrap justify-center gap-x-6 gap-y-1 text-sm font-medium text-ash-grey-200"
                        >
                            <Link
                                v-for="item in siteFooterNavItems"
                                :key="item.title"
                                :href="item.href"
                                class="transition hover:text-spicy-paprika-400"
                            >
                                {{ item.title }}
                            </Link>
                            <a
                                :href="feed().url"
                                class="transition hover:text-spicy-paprika-400"
                            >
                                RSS
                            </a>
                        </div>
                        <p class="text-sm text-ash-grey-500">
                            &copy; {{ currentYear }} {{ site.author }}. All
                            rights reserved.
                        </p>
                    </div>
                </ContainerInner>
            </div>
        </ContainerOuter>
    </footer>
</template>
