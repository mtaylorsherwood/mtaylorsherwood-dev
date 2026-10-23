<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        as: 'div',
    },
);
</script>

<template>
    <component
        :is="as"
        :class="cn(props.class, 'group relative flex flex-col items-start')"
    >
        <slot />
    </component>
</template>
