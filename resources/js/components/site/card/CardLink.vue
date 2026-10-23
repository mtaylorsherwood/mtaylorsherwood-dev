<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: NonNullable<InertiaLinkProps['href']>;
}>();
</script>

<template>
    <div
        class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-espresso-900/50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 sm:-inset-x-6 sm:rounded-2xl"
    />
    <Link :href="href">
        <span
            class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"
        />
        <span class="relative z-10">
            <slot />
        </span>
    </Link>
</template>
