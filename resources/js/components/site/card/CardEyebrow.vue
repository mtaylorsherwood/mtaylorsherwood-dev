<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: string;
        decorate?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        as: 'p',
        decorate: false,
    },
);
</script>

<template>
    <component
        :is="as"
        :class="
            cn(
                props.class,
                'relative z-10 order-first mb-3 flex items-center text-sm text-ash-grey-500',
                decorate && 'pl-3.5',
            )
        "
    >
        <span
            v-if="decorate"
            class="absolute inset-y-0 left-0 flex items-center"
            aria-hidden="true"
        >
            <span class="h-4 w-0.5 rounded-full bg-ash-grey-500" />
        </span>
        <slot />
    </component>
</template>
