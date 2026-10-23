<script setup lang="ts">
import { useId } from 'vue';

defineProps<{
    title: string;
}>();

const id = useId();
</script>

<template>
    <section
        :aria-labelledby="id"
        class="md:border-l md:border-ash-grey-700/40 md:pl-6"
    >
        <div
            class="grid max-w-3xl grid-cols-1 items-baseline gap-y-8 md:grid-cols-4"
        >
            <h2 :id="id" class="text-sm font-semibold text-ash-grey-100">
                {{ title }}
            </h2>
            <div class="md:col-span-3">
                <slot />
            </div>
        </div>
    </section>
</template>
