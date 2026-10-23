<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type Props = {
    variant?: 'primary' | 'secondary';
    href?: InertiaLinkProps['href'];
    class?: HTMLAttributes['class'];
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
});

const variantStyles = {
    primary:
        'bg-spicy-paprika-600 font-semibold text-spicy-paprika-50 hover:bg-spicy-paprika-500 active:bg-spicy-paprika-600 active:text-spicy-paprika-50/70',
    secondary:
        'bg-ash-grey-800/50 font-medium text-ash-grey-300 hover:bg-ash-grey-800 hover:text-ash-grey-50 active:bg-ash-grey-800/50 active:text-ash-grey-50/70',
} as const;

const classes = computed(() =>
    cn(
        'inline-flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm outline-offset-2 transition active:transition-none',
        variantStyles[props.variant],
        props.class,
    ),
);
</script>

<template>
    <Link v-if="href !== undefined" :href="href" :class="classes">
        <slot />
    </Link>
    <button v-else :class="classes">
        <slot />
    </button>
</template>
