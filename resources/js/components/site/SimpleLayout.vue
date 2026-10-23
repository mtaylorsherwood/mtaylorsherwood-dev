<script setup lang="ts">
import { useSlots } from 'vue';
import Container from '@/components/site/Container.vue';

defineProps<{
    title: string;
    intro: string;
}>();

const slots = useSlots();
</script>

<template>
    <Container class="mt-16 sm:mt-32">
        <header class="max-w-2xl">
            <h1
                class="text-4xl font-bold tracking-tight text-ash-grey-100 sm:text-5xl"
            >
                {{ title }}
            </h1>
            <p class="mt-6 text-base text-ash-grey-400">
                {{ intro }}
            </p>
        </header>
        <div v-if="slots.default" class="mt-16 sm:mt-20">
            <slot />
        </div>
    </Container>
</template>
