<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div :class="cn('relative px-4 sm:px-8 lg:px-12', props.class)">
        <div class="mx-auto max-w-2xl lg:max-w-5xl">
            <slot />
        </div>
    </div>
</template>
