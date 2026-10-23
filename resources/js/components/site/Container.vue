<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import ContainerInner from '@/components/site/ContainerInner.vue';
import ContainerOuter from '@/components/site/ContainerOuter.vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <ContainerOuter :class="props.class">
        <ContainerInner>
            <slot />
        </ContainerInner>
    </ContainerOuter>
</template>
