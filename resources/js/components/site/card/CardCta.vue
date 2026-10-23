<script setup lang="ts">
import { ChevronRightIcon } from '@/components/site/icons';
</script>

<template>
    <div
        aria-hidden="true"
        class="relative z-10 mt-4 flex items-center text-sm font-medium text-spicy-paprika-400"
    >
        <slot />
        <ChevronRightIcon class="ml-1 h-4 w-4 stroke-current" />
    </div>
</template>
