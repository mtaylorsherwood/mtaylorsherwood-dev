<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        :class="
            cn(
                props.class,
                'h-10 w-10 rounded-full bg-espresso-950/90 p-0.5 shadow-lg ring-1 ring-white/10 backdrop-blur-sm',
            )
        "
    >
        <slot />
    </div>
</template>
