<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import CardLink from '@/components/site/card/CardLink.vue';

withDefaults(
    defineProps<{
        as?: string;
        href?: InertiaLinkProps['href'];
    }>(),
    {
        as: 'h2',
    },
);
</script>

<template>
    <component
        :is="as"
        class="text-base font-semibold tracking-tight text-ash-grey-100"
    >
        <CardLink v-if="href !== undefined" :href="href">
            <slot />
        </CardLink>
        <slot v-else />
    </component>
</template>
