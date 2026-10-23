<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div :class="cn('sm:px-8', props.class)">
        <div class="mx-auto w-full max-w-7xl lg:px-8">
            <slot />
        </div>
    </div>
</template>
